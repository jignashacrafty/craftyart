@inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
@inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
@inject('helperController', 'App\Http\Controllers\HelperController')
@include('layouts.masterhead')
<div class="main-container designer-access-container">
    <div class="">
        <div class="min-height-200px">
            <div class="card-box">
                <div style="display: flex; flex-direction: column; height: 89vh; overflow: hidden;">

                    <div class="row justify-content-between">
                        <div class="col-md-3">
                            <h5 class="m-2">Contact Us Web</h5>
                        </div>

                        <div class="col-md-7">
                            @include('partials.filter_form ', [
                                'action' => route('contact_us_web'),
                            ])
                        </div>
                    </div>

                    <div class="scroll-wrapper table-responsive tableFixHead"
                        style="max-height: calc(110vh - 220px) !important;">
                        <table id="temp_table" style="table-layout: fixed; width: 100%;"
                            class="table table-striped table-bordered mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 0px;">Id</th>
                                    <th style="width: 20px;">Name</th>
                                    <th style="width: 60px;">Email</th>
                                    <th style="width: 20px;">Message</th>
                                    <th style="width: 70px;">System Info</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($ContactUses as $ContactUse)
                                    <tr>
                                        <td>{{ $ContactUse->id }}</td>
                                        <td>{{ $ContactUse->name }}</td>
                                        <td>{{ $ContactUse->email }}</td>
                                        <td>{{ $ContactUse->message }}</td>
                                        <td>
                                            @php
                                                $systemInfo = json_decode($ContactUse->system_info, true);
                                            @endphp

                                            @if ($systemInfo)
                                                <ul style="padding-left: 15px; margin: 0;">
                                                    <div class=" d-flex justify-content-around">
                                                        <div class="">
                                                            <li><strong>IP : </strong> {{ $systemInfo['ip'] ?? '-' }}
                                                            </li>
                                                            <li><strong>Mobile : </strong>
                                                                {{ $systemInfo['mobile'] ?? '-' }}
                                                            </li>
                                                            <li><strong>Tablet : </strong>
                                                                {{ $systemInfo['tablet'] ?? '-' }}
                                                            </li>
                                                            <li><strong>Desktop : </strong>
                                                                {{ $systemInfo['desktop'] ?? '-' }}
                                                            </li>
                                                            <li><strong>CPU Cores : </strong>
                                                                {{ $systemInfo['cpuCores'] ?? '-' }}</li>
                                                            <li><strong>Device : </strong>
                                                                {{ $systemInfo['device'] ?? '-' }}
                                                            </li>
                                                            <li><strong>Browser : </strong>
                                                                {{ $systemInfo['browser'] ?? '-' }}
                                                            </li>
                                                        </div>
                                                        <div class="">
                                                            <li><strong>Memory (GB) : </strong>
                                                                {{ $systemInfo['deviceMemory'] ?? '-' }}</li>


                                                            <li><strong>Platform : </strong>
                                                                {{ $systemInfo['platform'] ?? '-' }}
                                                            </li>
                                                            <li><strong>Language : </strong>
                                                                {{ $systemInfo['language'] ?? '-' }}
                                                            </li>

                                                            <li><strong>Resolution : </strong>
                                                                {{ $systemInfo['screenResolution'] ?? '-' }}</li>
                                                            <li><strong>Timezone : </strong>
                                                                {{ $systemInfo['timezone'] ?? '-' }}
                                                            </li>

                                                            <li><strong>Online:</strong>
                                                                @if (isset($systemInfo['online']))
                                                                    {{ $systemInfo['online'] ? 'Yes' : 'No' }}
                                                                @else
                                                                    -
                                                                @endif
                                                            </li>
                                                        </div>
                                                    </div>

                                                </ul>
                                            @else
                                                <span class="text-muted">No Info</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <hr class="my-1">
                @include('partials.pagination', ['items' => $ContactUses])
            </div>
        </div>
    </div>
</div>
@include('layouts.masterscript')
</body>
</html>
