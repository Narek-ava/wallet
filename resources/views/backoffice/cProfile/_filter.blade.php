<form action="" method="get" id="filter" class="row">
    <div class="col-md-12 mt-5 mb-4">
        @include('backoffice.partials.session-message')

        <div class="addNewUser row">
            <div class="col-md-2">
                @if($currentAdmin->hasPermissionInAnyProject([\App\Enums\BUserPermissions::ADD_EDIT_CLIENTS]))
                    <button type="button" class="btn btn-lg btn-primary themeBtn mb-4 mb-md-0"
                            data-toggle="modal" data-target="#newCProfile">Add new user
                    </button>
                @endif
            </div>
            <input type="hidden" name="type" id="sortDirection" value="{{$type}}">
            <input type="hidden" id="sort_direction" name="sortDirection" value="{{$sortDirection}}">
            <input type="hidden" id="sort_by" name="sort" value="{{$sort}}">
            <div class="col-md-2 textRed font-weight-bold text-center pt-2">
                <a href="{{route('backoffice.profiles', ['type' => $type])}}"
                   class="textRed font-weight-bold text-center pt-2">Refresh</a>
            </div>
            <div class="col-md-2 textRed font-weight-bold  text-center pt-2">
                <a href="{{ request()->fullUrlWithQuery(['export' => 1]) }} "
                   class="textRed font-weight-bold  text-center pt-2">Export (.csv)</a>
            </div>
            <div class="col-md-2">
                <input type="text" name="q" value="{{$q}}" class="filter_el" placeholder="{{ t('ui_search') }}">
            </div>
            <div class="col-md-2">
                <input type="text" name="ref" value="{{ request()->ref }}" class="filter_el"
                       placeholder="{{ t('referral_link') }}">
            </div>
        </div>
    </div>
    <div class="col-md-12 mb-5">
        <div class="addNewUser row">
            <div class="form-group col-md-2">
                <label for="inputEmail">Verification</label>
                <select class="w-100 filter_el" name="compliance_level">
                    <option value="">All</option>
                    @foreach($complianceLevelList as $verificationKey => $verificationName)
                        <option value="{{$verificationKey}}"
                                @if ($compliance_level === (string)$verificationKey)
                                selected
                            @endif
                        >{{$verificationName}}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-2">
                <label for="inputEmail">Status</label>
                <select class="w-100 filter_el" name="status">
                    <option value="">All</option>
                    @foreach($statusList as $statusId => $statusName)
                        <option value="{{$statusId}}"
                                @if ($status === (string)$statusId)
                                selected
                            @endif
                        >{{$statusName}}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-3">
                <label for="inputEmail" class="d-block">Last login</label>
                <input
                    value="{{$lastLoginFrom}}" data-provide="datepicker" name="lastLoginFrom"
                    data-date-format="yyyy-mm-dd"
                    class="filter_el form-control lastLogin" placeholder="From">
                <input value="{{$lastLoginTo}}" data-provide="datepicker" name="lastLoginTo"
                       data-date-format="yyyy-mm-dd"
                       class="filter_el form-control lastLogin" placeholder="To">
            </div>
            <div class="form-group col-md-3">
                <label for="inputEmail" class="d-block">Total Balance</label>
                <input type="email" id="inputEmail" class="form-control lastLogin" placeholder="From">
                <input type="email" id="inputEmail" class="form-control lastLogin" placeholder="To">

            </div>
            <div class="col-md-12">
                <div class="addNewUser row">
                    <div class="form-group col-md-2">
                        <label for="inputEmail" class="d-block">Project</label>
                        <select class="w-100 filter_el" name="project_id">
                            <option value="">All</option>
                            @foreach($activeProjects as $project)
                                <option @if($projectId == $project->id) selected
                                        @endif value="{{ $project->id  }}">{{ $project->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-2">
                        <label for="inputEmail" class="d-block">Manager</label>
                        <select class="w-100 filter_el" name="manager_id">
                            <option value="">All</option>
                            @foreach(\App\Models\Backoffice\BUser::accountManagersList() as $id => $email)
                                <option {{ $managerId == $id ? "selected":"" }} value="{{$id}}">{{$email}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>


