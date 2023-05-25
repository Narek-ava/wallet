<?php
/** @var \App\Models\Backoffice\BUser[] $bUsers*/
?>

@extends('backoffice.layouts.backoffice')

@section('title', t('title_clients_page'))

@section('content')
    <div class="row">

        <div class="col-md-12">
            <h2 class="mb-3 large-heading-section">{{ t('title_clients_page') }}</h2>
            <div class="row">
                <div class="col-md-5 d-flex justify-content-between">
                    <div class="balance mb-4">
                        {{ t('backoffice_profile_page_header_body') }}
                    </div>
                </div>
                @include('cabinet.partials.notification', ['notify' => getNotification(\Illuminate\Support\Facades\Auth::id()), 'admin' => true])
            </div>
        </div>
    </div>



    <div class="row mt-5">
        <h2>{{ t('ui_b_users') }}</h2>
        <div class="col-md-2">
            <a type="button" class="btn themeBtnWithoutHover" href="{{ route('b-users.create') }}">
                {{ t('create_new') }}
            </a>
        </div>
        <form id="filterForm" method="get" >
            <select name="status" class="ml-4" id="status" style="padding-right: 50px;">
                <option value=""> All</option>
                @foreach(App\Enums\AdminRoles::NAMES_STATUS as $key => $status)
                    <option value="{{ $key }}" @if(request()->status === strval($key)) selected @endif> {{ $status }} </option>
                @endforeach
            </select>
        </form>

        <div class="col-md-12">
            @if(session()->has('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert" id="successMessageAlert">
                    <h4>{{ session()->get('success') }}</h4>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif
        </div>

        <div class="col-md-12">
            <div class="row" id="merchantFormsSection">
                @foreach($bUsers as $key => $bUser)
                    <div class="col-md-3 b-users-forms-section"
                         style="cursor:pointer;">
                        <p class="mt-3 activeLink b-users-name">{{ $bUser->email }}</p>
                        @if($bUser->first_name || $bUser->last_name)
                            <p class="activeLink b-users-section-dates">Name: {{ $bUser->getFullName() }}</p>
                        @endif
                        <div class="b-users-section-dates">Role: {{ \App\Enums\AdminRoles::getName($bUser->is_super_admin) }}</div>
                        <div class="b-users-section-dates">Status: {{ \App\Enums\AdminRoles::NAMES_STATUS[$bUser->status] }}</div>
                        <p class="b-users-section-dates">Created: {{ $bUser->created_at ?? '-' }} </p>
                        @if(!$bUser->is_super_admin)
                            <a class="btn border-none b-users-section-roles   {{ $bUser->status ? 'text-success' : 'text-danger'}} " data-toggle="modal" data-target="#confirmModal{{$key}}"> {{ \App\Enums\AdminRoles::NAMES_STATUS[$bUser->status] }}
                            </a>
                            <a class="border-none" href="{{ route('b-users.edit', $bUser) }}" >
                                <img src="{{ config('cratos.urls.theme') }}images/edit_pencil.png" width="20"
                                     height="20"
                                     alt="">
                            </a>
                        @endif
                    </div>
                    @include('backoffice.b-users._confirm', ['key' =>$key])
                @endforeach
            </div>

        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $('#status').on('change', function () {
            $('#filterForm').submit()
        })
    </script>
@endsection
