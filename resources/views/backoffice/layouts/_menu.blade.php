<nav class="sidebar">
    <div class="users-info pt-3">
        <a class="text-left d-block p-3" href="{{ route('backoffice.profiles', ['type' => 1]) }}"><img src="{{ config('cratos.urls.theme') }}images/logo.svg" class="img-fluid sidebar-logo" alt=""></a>
        <div class="user-name pl-2 pl-lg-3 mt-0 mt-lg-3">{{ auth()->guard('bUser')->user()->getFullName() }} </div>
        <div class="user-name pl-3 mt-3">{{ config('cratos.company_details.name') }}</div>
        <a class="logout pl-3" href="#" onclick="event.preventDefault();document.getElementById('logout-form').submit();">Log out</a>
        <form id="logout-form" action="{{ route('backoffice.logout') }}" method="POST" style="display: none;">

        </form>
    </div>
    <div class="sidebar-sticky">
        <ul class="nav flex-column mt-5 pt-3">
            @if($currentAdmin->hasPermissionInAnyProject([\App\Enums\BUserPermissions::VIEW_PROVIDERS]))
                <li class="nav-item">
                    <a class="nav-link {{ activeMenu('backoffice.dashboard') }}"
                       href="{{route('backoffice.dashboard')}}">
                        {{__('profile.backoffice.dashboard')}}
                    </a>
                </li>
            @endif
            @if($currentAdmin->hasPermissionInAnyProject([\App\Enums\BUserPermissions::VIEW_CLIENTS]))
                <li class="nav-item">
                    <a class="nav-link dropdown-toggle  {{ activeMenu('backoffice.profiles') }} " data-toggle="collapse"
                       aria-expanded="false" href="#clients">
                        {{__('profile.backoffice.clients')}}
                    </a>
                    <ul class="collapse list-unstyled @isset($showClients) show @endisset" id="clients">
                        @foreach(\App\Models\Cabinet\CProfile::TYPES_LIST as $type => $name)
                            <li><a @if( isset($showClients) && $showClients == $type )class="active" @endif
                                href="{{route('backoffice.profiles',['type' => $type])}}"
                                   role="option"> {{t($name)}}</a></li>
                            @if(isset($profileId ) && isset($showClients) && $showClients == $type )
                                <ul class="collapse list-unstyled show">
                                    <li><a class="active" role="option"> {{$profileId}}</a></li>
                                </ul>
                            @endif
                        @endforeach
                    </ul>
                </li>
            @endif
            @if($currentAdmin->hasPermissionInAnyProject([\App\Enums\BUserPermissions::VIEW_OPERATION]))
                <li class="nav-item">
                    <a class="nav-link {{ activeMenu('backoffice.transactions') }}"
                       href="{{ route('backoffice.transactions') }}">
                        {{__('profile.backoffice.operations')}}
                    </a>
                </li>
            @endif
            @if($currentAdmin->hasPermissionInAnyProject([\App\Enums\BUserPermissions::VIEW_PAYMENT_FORMS]))
                <li class="nav-item">
                    <a class="nav-link {{ activeMenu('backoffice.payment.form') }} {{ activeMenu('backoffice.transaction.payment.form') }}"
                       href="{{ route('backoffice.payment.form') }}">
                        {{__('profile.backoffice.paymentForm')}}
                    </a>
                </li>
            @endif
            <li class="nav-item">
                <a class="nav-link {{ activeMenu('backoffice.notifications') }}" href="{{ route('backoffice.notifications') }}">
                    {{__('profile.backoffice.notifications')}}
                    @if($notifications_count)
                        <span class="notifications-count">{{$notifications_count}}</span>
                    @endif
                </a>
            </li>
                @if($currentAdmin->hasPermissionInAnyProject([\App\Enums\BUserPermissions::ADD_ANSWER_TICKETS]))
                    <li class="nav-item">
                        <a class="nav-link {{ activeMenu('backoffice.tickets') }}"
                           href="{{ route('backoffice.tickets') }}">
                            {{__('profile.backoffice.tickets')}}
                            @if($tickets_count)
                                <span class="notifications-count backofficeTicketsCount">{{$tickets_count}}</span>
                            @endif
                        </a>
                    </li>
                @endif
            <li class="nav-item">
                <a class="nav-link {{ activeMenu('backoffice.settings') }}" href="/backoffice/settings">
                    {{__('profile.backoffice.settings')}}
                </a>
            </li>
                @if($currentAdmin->hasPermissionInAnyProject([\App\Enums\BUserPermissions::VIEW_OPERATION]))
                    <li class="nav-item">
                        <a class="nav-link  {{ activeMenu('backoffice.reports') }} " href="/backoffice/reports">
                            {{__('profile.backoffice.reports')}}
                        </a>
                    </li>
                @endif
        </ul>
    </div>
</nav>
