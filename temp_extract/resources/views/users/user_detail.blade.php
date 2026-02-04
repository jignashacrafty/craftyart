   @inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
 @inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
 @inject('helperController', 'App\Http\Controllers\HelperController')
 @include('layouts.masterhead')
<div class="main-container">
  
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">
            <div class="pd-20 card-box mb-30">
                <form method="post" id="dynamic_form" enctype="multipart/form-data">

                    <span id="result"></span>

                    @csrf

                    <div class="row">

                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th scope="col">UID</th>
                                    <th scope="col">Picture</th>
                                    <th scope="col">Name</th>
                                    <th scope="col">Email</th>
                                    <th scope="col">Type</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td scope="row">{{ $userData['user']->uid }}</td>
                                    <td><img src="{{ config('filesystems.storage_url') }}{{ $userData['user']->photo_uri }}"
                                            style="max-width: 100px; max-height: 100px; width: auto; height: auto" />
                                    </td>
                                    <td scope="row">{{ $userData['user']->name }}</td>
                                    <td scope="row">{{ $userData['user']->email }}</td>
                                    <td scope="row">{{ $userData['user']->login_type }}</td>
                                </tr>
                            </tbody>
                        </table>

                    </div>

                    <br />

                    <div class="row">
                        <h5>Activities</h5>
                    </div>

                    <br />

                    <div class="row">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Last Login Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($userData['userAct'] as $user)
                                        <tr>
                                            <td scope="row">{{ $user->id }}</td>
                                            <td scope="row">{{ $user->last_login_time }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>
@include('layouts.masterscript')
</body>

</html>
