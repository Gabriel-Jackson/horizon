@extends('adminlte::page')

@section('meta_tags')
    <meta name="csrf-token" content="{{ csrf_token() }}">   
@endsection

<!-- Style sheets-->
@section('css')
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
    <link href="{{ asset(mix($cssFile, 'vendor/horizon')) }}" rel="stylesheet">
@endsection

@section('content')
<div id="horizon" v-cloak>  
        @section('sidebar', true)

        <aside class="main-sidebar {{ config('adminlte.classes_sidebar', 'sidebar-dark-primary elevation-4') }}">
            @if(config('adminlte.logo_img_xl'))
                @include('adminlte::partials.common.brand-logo-xl')
            @else
                @include('adminlte::partials.common.brand-logo-xs')
            @endif
            <div class="sidebar">
                <nav class="pt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column {{ config('adminlte.classes_sidebar_nav', '') }}"
                        data-widget="treeview" role="menu"
                        @if(config('adminlte.sidebar_nav_animation_speed') != 300)
                            data-animation-speed="{{ config('adminlte.sidebar_nav_animation_speed') }}"
                        @endif
                        @if(!config('adminlte.sidebar_nav_accordion'))
                            data-accordion="false"
                        @endif>
                        
                        <li class="nav-item ">
                            <router-link active-class="active" to="/dashboard" class="nav-link d-flex align-items-center pl-2">
                                <i class="nav-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path d="M0 3c0-1.1.9-2 2-2h16a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3zm2 2v12h16V5H2zm8 3l4 5H6l4-5z"></path>
                                    </svg>
                                </i>
                                <p>Dashboard</p>
                            </router-link>
                        </li>

                        <li class="nav-item ">
                            <router-link active-class="active" to="/queues" class="nav-link d-flex align-items-center pl-2">
                                <i class="nav-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path d="M1 1h18v2H1V1zm0 8h18v2H1V9zm0 8h18v2H1v-2zM1 5h18v2H1V5zm0 8h18v2H1v-2z"/>
                                    </svg>
                                </i>
                                <p>Filas</p>
                            </router-link>
                        </li>
                        
                        <li class="nav-item ">
                            <router-link active-class="active" to="/jobs/pending" class="nav-link d-flex align-items-center pl-2">
                                <i class="nav-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM7 6h2v8H7V6zm4 0h2v8h-2V6z"/>
                                    </svg>
                                </i>
                                <p>Jobs Pendentes</p>
                            </router-link>
                        </li>

                        <li class="nav-item ">
                            <router-link active-class="active" to="/jobs/completed" class="nav-link d-flex align-items-center pl-2">
                                <i class="nav-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM6.7 9.29L9 11.6l4.3-4.3 1.4 1.42L9 14.4l-3.7-3.7 1.4-1.42z"></path>
                                    </svg>
                                </i>
                                <p>Jobs Completos</p>
                            </router-link>
                        </li>

                        <li class="nav-item ">
                            <router-link active-class="active" to="/failed" class="nav-link d-flex align-items-center pl-2">
                                <i class="nav-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm1.41-1.41A8 8 0 1 0 15.66 4.34 8 8 0 0 0 4.34 15.66zm9.9-8.49L11.41 10l2.83 2.83-1.41 1.41L10 11.41l-2.83 2.83-1.41-1.41L8.59 10 5.76 7.17l1.41-1.41L10 8.59l2.83-2.83 1.41 1.41z"></path>
                                    </svg>
                                </i>
                                <p>Jobs Com Falha</p>
                            </router-link>
                        </li>
                    </ul>
                </nav>
                
            </div>
        </aside>
            
        <alert :message="alert.message"
            :type="alert.type"
            :auto-close="alert.autoClose"
            :confirmation-proceed="alert.confirmationProceed"
            :confirmation-cancel="alert.confirmationCancel"
            v-if="alert.type"></alert>

        
        
        @section('content_header')
            <div class="d-flex align-items-center py-4 header">
        
                <img class="logo" style="width: 30px; height: 30px;"
                    src="{{asset('/vendor/horizon/img/logoCotripal.png')}}"/>
        
                <h4 class="mb-0 ml-2">
                    <strong>Cotripal</strong> {{ config('app.name') ? ' - ' . config('app.name') : '' }}
                </h4>
            </div>
        @endsection

        @if (! $assetsAreCurrent)
            <div class="alert alert-warning">
                The published Horizon assets are not up-to-date with the installed version. To update, run:<br/><code>php artisan horizon:publish</code>
            </div>
        @endif

        @if ($isDownForMaintenance)
            <div class="alert alert-warning">
                This application is in "maintenance mode". Queued jobs may not be processed unless your worker is using the "force" flag.
            </div>
        @endif
        <router-view></router-view>
</div>
@endsection


@section('js')
    <!-- Global Horizon Object -->
    <script>
        window.Horizon = @json($horizonScriptVariables);
    </script>

    <script src="{{asset(mix('app.js', 'vendor/horizon'))}}"></script>
@endsection
