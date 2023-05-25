@extends('backoffice.layouts.backoffice')
@section('title', t('title_new_notifications_page'))

@section('content')
    @if(session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert" id="successMessageAlert">
            <h4>{{ session()->get('success') }}</h4>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    <div class="container-fluid">
        @include('backoffice.partials.notification-header')
        <div class="row ml-1">
            @if($currentAdmin->hasPermissionInAnyProject([\App\Enums\BUserPermissions::ADD_NEW_NOTIFICATIONS]))
                <div class="col-md-12 pl-0">
                    <form action="{{ route('backoffice.notifications.notify') }}" method="post">
                        @csrf
                        <input type="hidden" name="title" id="title">
                        <textarea name="message" rows="5" id="notificationTextarea">{{ old('message') }}</textarea>
                        @error('message')
                        <p class="textRed">{{ $message }}</p>
                        @enderror
                        <div class="row mt-4 mb-4">
                            <div class="col-md-3">
                                <label class="activeLink" for="tag">Tags</label>
                                <select name="tag" id="tag" class="selectFluid">
                                    <option value=""></option>
                                    @foreach($tags as $key => $tag)
                                        <option value="{{ is_int($key) ? $tag : $key }}">{{ $tag }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-">
                                <label class="activeLink" for="project_id">Project</label>
                                <select data-permission="{{ \App\Enums\BUserPermissions::ADD_NEW_NOTIFICATIONS }}"
                                        name="project_id" id="project_id" class="selectFluid projectSelect" required>
                                    <option value=""></option>
                                    @foreach($activeProjects as $project)
                                        <option @if($projectId == $project->id) selected
                                                @endif value="{{ $project->id  }}">{{ $project->name }}</option>
                                    @endforeach
                                </select>
                                <div class="error text-danger projectSelectError"></div>
                                @error('project_id')
                                <p class="textRed">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="col-md-2">
                                <label class="activeLink">Recipients</label>
                                <select name="recipients" id="toAllUsers" class="selectFluid">
                                    <option value=""></option>
                                    @foreach(\App\Enums\NotificationRecipients::RECIPIENTS as $key => $recipient)
                                        <option
                                            value="{{ $key }}" {{ old('recipients') == $key ? 'selected' : '' }}>{{ t($recipient) }}</option>
                                    @endforeach
                                </select>
                                @error('recipients')
                                <p class="textRed">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="col-md-2">
                                <label class="activeLink" for="profile_id">Profile ID</label>
                                <select disabled name="profile_id" id="profile_id" class="selectFluid">
                                </select>
                                @error('profile_id')
                                <p class="textRed">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="col-md-2 mt-4">
                                <button type="submit"
                                        class="btn btn-lg btn-primary themeBtnDark">{{ t('ui_add_new_notification') }}</button>
                            </div>
                            <span id="titleSuccessfulMessage" class="text-success"></span>
                        </div>
                    </form>


                    <div class="col-md-2 mt-3 ml-0 pl-0">
                                    <button data-toggle="modal" data-target="#addNewTagModal"
                                          class="btn btn-lg btn-primary themeBtn mb-4 selectFluid">{{ t('ui_add_new_tag') }}</button>
                    </div>
                </div>
            @endif
            <div class="width20 activeLink">{{ t('ui_number_notification') }}</div>
            <div class="width20 activeLink">{{ t('ui_date_time') }}</div>
            <div class="width15 activeLink">{{ t('ui_open_rate') }}</div>
            <div class="width15 activeLink">{{ t('ui_for_whom') }}</div>
            <div class="width15 activeLink">{{ t('ui_status') }}</div>
            <div class="width10 activeLink"></div>
            @foreach($notificationsWithPaginate as $notification)
                <div class="createdNotifications">
                    <div class="width20 createdNotificationsItems activeLink">{{ $notification->id }}</div>
                    <div class="width20 createdNotificationsItems activeLink">{{ $notification->updated_at }}</div>
                    <div class="width15 createdNotificationsItems activeLink">
                        <span class="text-danger">{{ $notification->viewedNotificationsCount }}</span>/{{ $notification->allNotificationUsersCount }}
                    </div>
                    <div class="width15 createdNotificationsItems activeLink">{{ array_key_exists($notification->recepient, \App\Enums\NotificationRecipients::RECIPIENTS) ? t(\App\Enums\NotificationRecipients::RECIPIENTS[$notification->recepient]) : '' }}</div>
                    <div class="width15 createdNotificationsItems activeLink"><span class="{{ $notification->allNotificationUsersCount ? 'text-success' : 'text-danger' }}">{{ $notification->allNotificationUsersCount ? 'Delivered' : 'Not Send' }}</span></div>
                    <div class="width10 createdNotificationsItems notificationCabinetMoreMessageDown">
                        <span class="link-default seeMore cursor-pointer text-nowrap" href="#" data-notification-id="{{ $notification->id }}">
                            See Details <i class="fa fa-angle-toggle" aria-hidden="true"></i>
                        </span>
                    </div>
                    <div class="createdNotificationsInfo">
                        <div class="shortBodyMessage{{ $notification->id }}">
                            {!! \Illuminate\Support\Str::limit($notification->shortBody, 160, $end='...') !!}
                        </div>
                        <div class="bodyMessage{{ $notification->id }} display-none">
                            <div class="width20 createdNotificationsInfoItems activeLink">{{ $notification->bUser ? $notification->bUser->email : 'System' }}</div>
                            <div class="width20 createdNotificationsInfoItems activeLink">{{ \Illuminate\Support\Facades\Lang::has('cratos.'.$notification->title_message) ? t($notification->title_message, json_decode($notification->title_params, true)) : $notification->title_message }}</div>
                            <div class="width55 createdNotificationsInfoItems">{!! $notification->body !!}</div>
                        </div>
                    </div>
                </div>
            @endforeach
            {{ $notificationsWithPaginate->appends(request()->query())->links() }}
        </div>

        @if($currentAdmin->hasPermissionInAnyProject([\App\Enums\BUserPermissions::ADD_NEW_NOTIFICATIONS]))
            <div class="modal fade" id="addNewTagModal" tabindex="-1" role="dialog"
                 aria-labelledby="complianceModalTitle" aria-hidden="true">
                <div class="modal-dialog" role="document" style="max-width: 500px">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="complianceModalTitle">{{t('ui_menu_add_new_tag')}}</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body" id="newTagContainer">
                            <p>{{ t('ui_please_add_tag') }}</p>
                            <div class="row pl-3 pr-3">
                                <input type="text" class="selectFluid" id="modalTitleValue">
                            </div>
                            <span class="text-success" id="addTitleSuccessMessage"></span>
                            <span class="text-danger" id="addTitleDangerMessage"></span>
                        </div>
                        <div class="modal-footer" id="newTagContainer">
                            <button class="btn btn-lg btn-primary themeBtn mb-4" id="addTitle">{{ t('add') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            $('#profile_id').select2({
                placeholder: "Select Profile ID",
                val: false,
                width:'100%',
            });

            $('body').on('change', '#tag', function () {
                let title = $(this).val();
                $('#title').val(title);
                $.ajax({
                    url: 'notification-body/'+title,
                    success: function (data) {
                        $('#notificationTextarea').val(data);
                    }
                })
            });
            $('body').on('click', '.seeMore', function () {
                let notificationId = $(this).data('notification-id');
                let notificationBlock = $(this).parent();
                if(notificationBlock.hasClass('notificationCabinetMoreMessageUp')) {
                    notificationBlock.removeClass('notificationCabinetMoreMessageUp').addClass('notificationCabinetMoreMessageDown');
                    $('.shortBodyMessage'+notificationId).removeClass('display-none');
                    $('.bodyMessage'+notificationId).addClass('display-none');
                } else if(notificationBlock.hasClass('notificationCabinetMoreMessageDown')) {
                    notificationBlock.removeClass('notificationCabinetMoreMessageDown').addClass('notificationCabinetMoreMessageUp');
                    $('.shortBodyMessage'+notificationId).addClass('display-none');
                    $('.bodyMessage'+notificationId).removeClass('display-none');
                }
            })


            $('#project_id, #toAllUsers').on('change', function () {
                let profileIdContainer = $('#profile_id');

                let projectId = $('#project_id').val();
                let recipient = $('#toAllUsers').val();
                let url = "{{ route('get.profile.names') }}"
                if (recipient == {{ \App\Enums\NotificationRecipients::CURRENT_CLIENT }} && projectId) {
                    $.ajax({
                        url: url,
                        type: 'get',
                        data: {
                            'project_id': projectId,
                        },
                        success: (data) => {
                            profileIdContainer.prop('disabled', false)
                            profileIdContainer.prop('required', true)
                            profileIdsHtml = '';
                            data.profiles.forEach(function (profileId) {
                                profileIdsHtml += '<option value="' + profileId + '">' + profileId + '</option>'
                            })
                            profileIdContainer.html(profileIdsHtml)
                        }
                    });
                } else {
                    profileIdContainer.prop('disabled', true)
                    profileIdContainer.prop('required', false)
                    profileIdContainer.html('')
                }
            })
        });


    </script>
@endsection
