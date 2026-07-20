<!DOCTYPE html>
<html lang="en">
<head>
	@include('layouts.core._head')

	@include('layouts.core._script_vars')

	@yield('head')

	@php
		$themeCustomer = Auth::user()->customer;
		$themeMode = $themeCustomer ? $themeCustomer->theme_mode : 'light';
		$colorScheme = $themeCustomer ? $themeCustomer->getColorScheme() : 'default';
		$menuLayout = $themeCustomer ? $themeCustomer->getMenuLayout() : 'left';
	@endphp

	@if (getThemeMode($themeMode, session('customer-auto-theme-mode')) == 'dark')
		<meta name="theme-color" content="{{ getThemeColor($colorScheme) }}">
	@elseif ($menuLayout == 'left')
		<meta name="theme-color" content="#eff3f5">
	@endif

	<script>
		@if ($themeMode == 'auto')
			var ECHARTS_THEME = isDarkMode() ? 'dark' : null

			// auto detect dark-mode
			$(function() {
				autoDetechDarkMode('{{ action('AccountController@saveAutoThemeMode') }}');
			});
		@else
			var ECHARTS_THEME = '{{ $themeMode == 'dark' ? 'dark' : null }}';
		@endif
	</script>

    <!-- Theme -->
    <link rel="stylesheet" type="text/css" href="{{ AppUrl::asset('core/css/theme/' . $colorScheme . '.css') }}">
</head>
<body class="theme-{{ $colorScheme }} {{ $menuLayout }}bar
	{{ $menuLayout }}bar-{{ session('customer-leftbar-state') }} state-{{ session('customer-leftbar-state') }}
	fullscreen-search-box
	mode-{{ getThemeMode($themeMode, session('customer-auto-theme-mode'))  }}
">
	@if(config('app.cartpaye'))
		@include('layouts.core._menu_frontend_cartpaye')
    @elseif(config('app.store'))
		@include('layouts.core._menu_frontend_store')
	@elseif(config('app.brand'))
		@include('layouts.core._menu_frontend_brand')
	@elseif (!config('app.saas'))
		@include('layouts.core._menu_frontend_single')
	@else
		@include('layouts.core._menu_frontend_saas')
	@endif

	

	@include('layouts.core._middle_bar')

	<main class="container page-container px-3">
		@include('layouts.core._headbar_frontend')

        @if (config('app.store'))
            @include('layouts.core._topbar_frontend')
        @endif
		
		@yield('page_header')

		<!-- display flash message -->
		@include('layouts.core._errors')

		<!-- main inner content -->
		@yield('content')

		<!-- Footer -->
		@include('layouts.core._footer')
	</main>

	<!-- Admin area -->
	@include('layouts.core._admin_area')

	@if (!config('config.saas'))
		<!-- Admin area -->
		@include('layouts.core._loginas_area')
	@endif

	<!-- Notification -->
	@include('layouts.core._notify')
	@include('layouts.core._notify_frontend')

	<!-- display flash message -->
	@include('layouts.core._flash')

	<script>
		var wizardUserPopup;

		$(function() {
			// auto detect dark mode


			// Customer color scheme | menu layout wizard
			@if (false)
				$(function() {
					wizardUserPopup = new Popup({
						url: '{{ action('AccountController@wizardColorScheme') }}',
					});
					wizardUserPopup.load();
				});
			@endif
			
			@if (null !== Session::get('orig_admin_id') && Auth::user()->admin)
				notify({
					type: 'warning',
					message: `{!! trans('messages.current_login_as', ["name" => Auth::user()->customer->displayName()]) !!}<br>{!! trans('messages.click_to_return_to_origin_user', ["link" => action("Admin\AdminController@loginBack")]) !!}`,
					timeout: false,
				});
			@endif
		
			@if (null !== Session::get('orig_admin_id') && Auth::user()->admin)
				notify({
					type: 'warning',
					message: `{!! trans('messages.site_is_offline') !!}`,
					timeout: false,
				});
			@endif
		})
			
	</script>

	{!! \Acelle\Model\Setting::get('custom_script') !!}
</body>
</html>