@extends('layouts.core.login_slider')

@section('title', trans('messages.login'))

@section('content')

    <link type="text/css" rel="stylesheet" href="{{ AppUrl::asset('core/lightslider/css/lightslider.css') }}" />
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    <script src="{{ AppUrl::asset('core/lightslider/js/lightslider.js') }}"></script>

    <script>
        function addButtonLoadingEffect(button) {
            button.addClass('button-loading');
            button.prepend('<div class="loader"></div>');
        }

        function removeButtonLoadingEffect(button) {
            button.removeClass('button-loading');
            button.find('.loader').remove();
        }

        $(document).ready(function() {
            $(".login-slider").lightSlider({
                item: 1,
                speed: 400,
                controls: true,
                auto: true,
                loop: true,
                pause: 4000,
            });
        });
    </script>

    <style>
        @-webkit-keyframes load8 {
            0% {
                -webkit-transform: rotate(0deg);
                transform: rotate(0deg);
            }

            100% {
                -webkit-transform: rotate(360deg);
                transform: rotate(360deg);
            }
        }

        @keyframes load8 {
            0% {
                -webkit-transform: rotate(0deg);
                transform: rotate(0deg);
            }

            100% {
                -webkit-transform: rotate(360deg);
                transform: rotate(360deg);
            }
        }

        .button-loading {
            opacity: 0.8;
            pointer-events: none;
            padding-right: 32px !important;
            position: relative;
        }

        .button-loading .loader,
        .button-loading .loader:after {
            border-radius: 50%;
            width: 2em;
            height: 2em;
        }

        .button-loading .loader {
            font-size: 10px;
            z-index: 1000;
            position: relative;
            text-indent: -9999em;
            border-top: 0.2em solid rgba(0, 0, 0, 0.4);
            border-right: 0.2em solid rgba(0, 0, 0, 0.4);
            border-bottom: 0.2em solid rgba(0, 0, 0, 0.4);
            border-left: 0.2em solid #ddd;
            -webkit-transform: translateZ(0);
            -ms-transform: translateZ(0);
            transform: translateZ(0);
            -webkit-animation: load8 1.1s infinite linear;
            animation: load8 1.1s infinite linear;
            margin: 4px 0 0 5px;
            /* position: absolute; */
            display: inline-block;
            top: 4px;
            margin-top: -12px;
            margin-right: 7px;
        }

        .button-primary.button-loading .loader {
            border-top: 0.2em solid rgba(255, 255, 255, 0.2);
            border-right: 0.2em solid rgba(255, 255, 255, 0.2);
            border-bottom: 0.2em solid rgba(255, 255, 255, 0.2);
            border-left: 0.2em solid #ffffff;
        }

        form.loading {
            pointer-events: none;
        }

        form.loading input {
            background: rgba(255, 255, 255, 0.7)
        }
    </style>

    <div class="login-container dark flex" style="height:100vh">
        <div class="col-md-12 pd-lvl5 position-relative flex justify-content-center align-items-center">
            <form class="login-box-content mlck-box" method="POST" action="{{ url('/login') }}">
                {{ csrf_field() }}
                <div class="text-center">
                    <img class="mlck-logo" src="{{ getSiteLogoUrl('dark') }}" alt="">
                    <h2 class="mlck-subtitle">Ne bucurăm să te revedem</h2>
                </div>

                <!-- Flash messages -->
                @foreach (['danger', 'warning', 'info', 'error'] as $msg)
                    @php
                        $class = $msg;
                        if ($msg == 'error') {
                            $class = 'danger';
                        }
                    @endphp
                    @if (Session::has('alert-' . $msg))
                        <div class="alert alert-{{ $class }} alert-noborder alert-dismissible">
                            <strong>{{ trans('messages.' . $msg) }}</strong>
                            <p class="mb-0">{!! preg_replace('/[\r\n]+/', ' ', Session::get('alert-' . $msg)) !!}</p>
                        </div>
                    @endif
                @endforeach

                @include('helpers.form_control', [
                    'type' => 'email',
                    'class' => 'mlck-input',
                    'name' => 'email',
                    'label' => trans('messages.login.email'),
                    'value' => old('email') ? old('email') : demo_auth()['email'],
                ])

                @include('helpers.form_control', [
                    'type' => 'password',
                    'class' => 'mlck-input',
                    'name' => 'password',
                    'label' => trans('messages.login.password'),
                    'value' => demo_auth()['password'],
                ])

                <div class="form-group">
                    <label class="custom-control custom-checkbox">
                        <input name="remember" {{ old('remember') ? 'checked' : '' }} value="checked" type="checkbox"
                            class="custom-control-input">
                        <span class="custom-control-indicator"></span>
                        <span class="custom-control-description text-small">{{ trans('messages.login.remember_me') }}</span>
                    </label>
                </div>

                <div class="form-group">
                    <button type="submit" class="mlck-btn">
                        {{ trans('messages.login') }}
                    </button>
                    @if (
                        \Acelle\Model\Setting::get('oauth.google_enabled') == 'yes' ||
                            \Acelle\Model\Setting::get('oauth.facebook_enabled') == 'yes')
                        <div class="socialite_buttons text-center">
                            @if (\Acelle\Model\Setting::get('oauth.google_enabled') == 'yes')
                                <a href="{{ action('AuthController@googleRedirect') }}" class="socialite-link">
                                    <img src="{{ url('images/google-login.svg') }}" />
                                    <span class="mlck-social-text">Continuă cu Google</span>
                                </a>
                            @endif
                        </div>
                    @endif
                    <div class="text-center mt-lvl3">
                        <a class="btn btn-link text-center" href="{{ url('/password/reset') }}">
                            {{ trans('messages.forgot_password') }}
                        </a>
                        @if (config('app.saas') && \Acelle\Model\Setting::get('enable_user_registration') == 'yes')
                            <a href="{{ url('/users/register') }}"
                                class="btn btn-link">{{ trans('messages.login.create_an_account') }}</a>
                        @endif
                    </div>
                </div>
            </form>

            <div class="mlck-copyright">
                MailClick © 2025 Toate drepturile rezervate.
            </div>
        </div>
        {{-- <div class="col-md-9 phone-hide right-col">
            <div class="full-height full-width" style="display: flexs">
                <!--<h1 class="login-bg-title full-width pd-lvl3 text-white">Acelle Funnel</h1>-->
                <div class="login-slider-container">
                    <ul class="lightSlider login-slider">
                        <li class="slide" style="display: flex; justify-content: center; align-items: center">
                            <div style="height: 500px;">
                                <div class="text-center">
                                    <img height="100" src="{{ url('images/track_every_message.png') }}" />
                                </div>
                                <h2>Complete Delivery Tracking</h2>
                                <h4>Track every single message sent out for your campaign</h4>
                                <ul>
                                    <li class="d-flex align-items-center"><span
                                            class="material-symbols-rounded mr-2 text-info">done</span> <span>Track your
                                            messages opens & clicks</span></li>
                                    <li class="d-flex align-items-center"><span
                                            class="material-symbols-rounded mr-2 text-info">done</span> <span>Automatically
                                            handle bounce & feedback</span></li>
                                    <li class="d-flex align-items-center"><span
                                            class="material-symbols-rounded mr-2 text-info">done</span> <span>Measure your
                                            campaign performance with insight reports</span></li>
                                </ul>
                            </div>
                        </li>
                        <li class="slide" style="display: flex; justify-content: center; align-items: center">
                            <div style="">
                                <div class="text-center">
                                    <img height="100" src="{{ url('images/open-to-customization-and-evolve.png') }}" />
                                </div>
                                <h2>Written in PHP, on top of LARAVEL 8</h2>
                                <h4>Open to full customization and rebranding</h4>
                                <p>Laravel is an free and open-source PHP framework, designed for the development of robust
                                    web applications succeeding the MVC pattern. With PHP/Laravel, code maintenance is
                                    easier than ever as your business is growing</p>
                                <ul>
                                    <li class="d-flex align-items-center"><span
                                            class="material-symbols-rounded mr-2 text-info">done</span> <span>Coded in PHP
                                            7x and 8x on top of Laravel 8</span></li>
                                    <li class="d-flex align-items-center"><span
                                            class="material-symbols-rounded mr-2 text-info">done</span> <span>Backed by
                                            MySQL 5.x</li>
                                </ul>
                            </div>
                        </li>
                        <li class="slide" style="display: flex; justify-content: center; align-items: center">
                            <div style="">
                                <div class="text-center">
                                    <img height="100" src="{{ url('images/automation-illustration.png') }}" />
                                </div>
                                <h2>Full-featured Automation / Auto-responder</h2>
                                <h4>Automate your campaign with email workflow editor</h4>
                                <ul>
                                    <li class="d-flex align-items-center"><span
                                            class="material-symbols-rounded mr-2 text-info">done</span> <span>Start your
                                            email campaigns in response to event triggers</span></li>
                                    <li class="d-flex align-items-center"><span
                                            class="material-symbols-rounded mr-2 text-info">done</span> <span>Automatically
                                            respond to your recipient activities (open/click)</span></li>
                                    <li class="d-flex align-items-center"><span
                                            class="material-symbols-rounded mr-2 text-info">done</span> <span>Design your
                                            marketing with email automation workflow</span></li>
                                </ul>
                            </div>
                        </li>
                        <li class="slide" style="display: flex; justify-content: center; align-items: center">
                            <div style="">
                                <div class="text-center">
                                    <img height="100" src="{{ url('images/acelle_mail_payment_transparency.png') }}" />
                                </div>
                                <h2>Designed as an SaaS framework</h2>
                                <h4>Provide your own email service to the world</h4>
                                <p>Manage your service plans and subscription. Get paid by your users via PayPal or Credit
                                    Card to your Stripe / Braintree / Paddle account</p>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>


        </div> --}}
    </div>
    <script>
        $('.login-button').on('click', function(e) {
            e.preventDefault();

            $(this).html('{{ trans('messages.login.please_wait') }}');

            $(this).closest('form').addClass('loading');

            addButtonLoadingEffect($(this));

            $(this).closest('form').submit();
        });
    </script>
@endsection
