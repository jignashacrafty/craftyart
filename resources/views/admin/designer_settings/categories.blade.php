@inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
@inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
@include('layouts.masterhead')

<style>
    .designer-system-container {
        background: #f8f9fa;
        min-height: 100vh;
        padding: 20px;
    }
    
    .system-card {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        overflow: hidden;
    }
    
    .system-header {
        padding: 20px;
        border-bottom: 1px solid #e9ecef;
        background: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .system-title {
        font-size: 20px;
        font-weight: 600;
        color: #212529;
        margin: 0;
    }
    
    .system-subtitle {
        font-size: 14px;
        color: #6c757d;
        margin: 5px 0 0 0;
    }
    
    .system-table-wrapper {
        overflow-x: auto;
    }
    
    .system-table {
        width: 100%;
        margin: 0;
        border-collapse: separate;
        border-spacing: 0;
    }
    
    .system-table thead th {
        background: #f8f9fa;
        color: #495057;
        font-weight: 600;
        font-size: 13px;
        text-transform: uppercase;
        padding: 15px 12px;
        border-bottom: 2px solid #dee2e6;
        white-space: nowrap;
    }
    
    .system-table tbody td {
        padding: 12px;
        border-bottom: 1px solid #e9ecef;
        vertical-align: middle;
        font-size: 14px;
        color: #495057;
    }
    
    .system-table tbody tr:hover {
        background-color: #f8f9fa;
    }
    
    .status-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 500;
    }
    
    .status-active {
        background: #d4edda;
        color: #155724;
    }
    
    .status-inactive {
        background: #f8d7da;
        color: #721c24;
    }
    
    .btn-action {
        padding: 6px 12px;
        border-radius: 4px;
        font-size: 13px;
        font-weight: 500;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
        margin: 2px;
        display: inline-block;
        white-space: nowrap;
    }
    
    .actions-cell {
        white-space: nowrap;
        min-width: 150px;
    }
    
    .btn-info-action {
        background: #17a2b8;
        color: white;
    }
    
    .btn-info-action:hover {
        background: #138496;
    }
    
    .btn-edit {
        background: #ffc107;
        color: #212529;
    }
    
    .btn-edit:hover {
        background: #e0a800;
    }
    
    .btn-toggle {
        background: #6c757d;
        color: white;
    }
    
    .btn-toggle:hover {
        background: #5a6268;
    }
    
    .btn-delete {
        background: #dc3545;
        color: white;
    }
    
    .btn-delete:hover {
        background: #c82333;
    }
    
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #6c757d;
    }
    
    .empty-state i {
        font-size: 48px;
        margin-bottom: 16px;
        opacity: 0.5;
    }
</style>

<div class="main-container">
    <div class="designer-system-container">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        @endif

        <div class="system-card">
            <div class="system-header">
                <div>
                    <h1 class="system-title">Designer Categories</h1>
                    <p class="system-subtitle">Manage designer interests and specializations</p>
                </div>
                <button class="btn btn-primary" onclick="openAddModal()">
                    <i class="fa fa-plus"></i> Add New Category
                </button>
            </div>

            <div class="system-table-wrapper">
                <table class="system-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>NAME</th>
                            <th>SLUG</th>
                            <th>DESCRIPTION</th>
                            <th>SORT ORDER</th>
                            <th>STATUS</th>
                            <th>ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $category)
                        <tr>
                            <td><strong>#{{ $category->id }}</strong></td>
                            <td><strong>{{ $category->name }}</strong></td>
                            <td><code>{{ $category->slug }}</code></td>
                            <td>{{ $category->description }}</td>
                            <td>{{ $category->sort_order }}</td>
                            <td>
                                @if($category->is_active)
                                    <span class="status-badge status-active">Active</span>
                                @else
                                    <span class="status-badge status-inactive">Inactive</span>
                                @endif
                            </td>
                            <td class="actions-cell">
                            <button class="btn-action btn-edit" onclick="openEditModal({{ $category->id }}, '{{ addslashes($category->name) }}', '{{ addslashes($category->description) }}', '{{ $category->icon }}', {{ $category->sort_order }})">
                                <i class="fa fa-edit"></i>
                            </button>
                            <form action="{{ route('designer_system.categories.toggle', $category->id) }}" 
                                  method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn-action btn-toggle">
                                    <i class="fa fa-toggle-{{ $category->is_active ? 'on' : 'off' }}"></i>
                                </button>
                            </form>
                            <form action="{{ route('designer_system.categories.delete', $category->id) }}" 
                                  method="POST" style="display:inline;" 
                                  onsubmit="return confirm('Are you sure you want to delete this category?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-action btn-delete">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                        @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <i class="fa fa-tags"></i>
                                    <p>No designer categories found</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div id="addModal" class="modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('designer_system.categories.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add New Category</h5>
                    <button type="button" class="modal-close" onclick="closeAddModal()">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Name *</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Icon (optional)</label>
                        <input type="text" name="icon" class="form-control" placeholder="fa-icon-name">
                    </div>
                    <div class="form-group">
                        <label>Sort Order</label>
                        <input type="number" name="sort_order" class="form-control" value="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeAddModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Category</h5>
                    <button type="button" class="modal-close" onclick="closeEditModal()">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Name *</label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Icon (optional)</label>
                        <input type="text" name="icon" id="edit_icon" class="form-control" placeholder="fa-icon-name">
                    </div>
                    <div class="form-group">
                        <label>Sort Order</label>
                        <input type="number" name="sort_order" id="edit_sort_order" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openAddModal() {
    document.getElementById('addModal').classList.add('show');
}

function closeAddModal() {
    document.getElementById('addModal').classList.remove('show');
}

function openEditModal(id, name, description, icon, sortOrder) {
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_description').value = description || '';
    document.getElementById('edit_icon').value = icon || '';
    document.getElementById('edit_sort_order').value = sortOrder;
    document.getElementById('editForm').action = '{{ url("designer-system/categories") }}/' + id;
    document.getElementById('editModal').classList.add('show');
}

function closeEditModal() {
    document.getElementById('editModal').classList.remove('show');
}

// Close modal when clicking outside
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.classList.remove('show');
    }
}
</script>

@include('layouts.footer')
