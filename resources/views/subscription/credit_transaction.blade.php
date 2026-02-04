@include('layouts.masterhead')
<div class="main-container">
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">
            <div class="card-box mb-30">
                <div class="pb-20">

                    <div id="DataTables_Table_0_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">

                        <div class="row">
                            <div class="col-sm-12 col-md-3">
                                <div class="pd-20">
                                    <h3 style="font-size: larger;color:black;">Credits Transaction Logs</h3>
                                </div>
                            </div>

                            <div class="col-sm-12 col-md-9">
                                <div class="pt-20">
                                    <form action="{{ route('credit_transaction_logs') }}" method="GET">
                                        <div class="form-group">
                                            <div id="DataTables_Table_0_filter" class="dataTables_filter">
                                                <label>Search:<input type="text" class="form-control" name="query"
                                                                     placeholder="Search here....."
                                                                     value="{{ request()->input('search') }}"></label> <button
                                                    type="submit" class="btn btn-primary">Search</button>
                                            </div>
                                        </div>
                                    </form>

                                </div>
                            </div>
                        </div>

                        <div class="col-sm-12 table-responsive">
                            <table class="table table-striped">
                                <thead>
                                <tr>
                                    <th>***</th>
                                    <th>User ID</th>
                                    <th>User Name</th>
                                    <th>Ref ID</th>
                                    <th>Txn ID</th>
                                    <th>Type</th>
                                    <th>Reason</th>
                                    <th>Debited</th>
                                    <th>Credited</th>
                                    <th>Created At</th>

                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($transactions as $transaction)
                                <tr>
                                    <td class="table-plus">{{ $transaction->id }}</td>

                                    <td>{{ $transaction->user_id }}</td>

                                    <td>{{ $transaction->user->name }}</td>

                                    <td>{{ $transaction->ref_id ?? "-" }}</td>

                                    <td>{{ $transaction->txn_id ?? "-" }}</td>

                                    <td>{{ $transaction->type ?? "-" }}</td>

                                    <td>{{ $transaction->reason ?? "-" }}</td>

                                    <td>{{ $transaction->credited }} </td>

                                    <td>{{ $transaction->debited }}</td>

                                    <td>{{ $transaction->created_at }}</td>

                                </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="row">
                            <div class="col-sm-12 col-md-5">
                                <div class="dataTables_info" id="DataTables_Table_0_info" role="status"
                                     aria-live="polite">{{$str_count}}</div>
                            </div>
                            <div class="col-sm-12 col-md-7">
                                <div class="dataTables_paginate paging_simple_numbers" id="DataTables_Table_0_paginate">
                                    <ul class="pagination">
                                        {{ $transactions->appends(request()->input())->links('pagination::bootstrap-4') }}
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

@include('layouts.masterscript')

</body>
</html>