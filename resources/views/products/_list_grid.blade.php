@if ($products->count() > 0)
	<div class="row mt-4">
		@foreach ($products as $key => $product)
			<div class="col-md-3 col-sm-6 col-lg-3 mb-4 d-flex">
				<div class="card mb-0 box-shadow w-100 d-flex flex-column">
					<span class="product-image-box">
						<img class="card-img-top" src="{{ $product->getImageUrl() }}" style="height: 100%; width: auto; display: block;">
					</span>
					<div class="card-body p-3 d-flex flex-column flex-grow-1">
						<h5 title="{{ $product->title }}" class="fw-600 mt-1 mb-2 xtooltip" style="display: -webkit-box;
							-webkit-box-orient: vertical;
							-webkit-line-clamp: 2;
							overflow: hidden;
							text-overflow: ellipsis;">{{ $product->title }}</h5>

						{{-- Category --}}
						<span class="d-block text-muted mb-2" style="font-size:12px;">
							<span class="material-symbols-rounded me-1" style="font-size:14px;">category</span>
							{{ $product->getCategoryLabel() }}
						</span>

						<div class="mt-auto">
							<hr class="my-2">

							{{-- Price + Source --}}
							<div class="row">
								<div class="col-5 text-start">
									<span class="fw-600" style="font-size:14px;">{{ number_format($product->price, 0) }} RON</span>
								</div>
								<div class="col-7 text-end">
									<span class="text-muted" style="font-size:12px;">
										<span class="material-symbols-rounded me-1" style="font-size:14px;">storefront</span>
										{{ $product->source ? $product->source->getName() : '—' }}
									</span>
								</div>
							</div>

							<hr class="my-2">

							{{-- Promo Score + Margin --}}
							<div class="row">
								<div class="col-6 text-start">
									<span class="d-block text-muted" style="font-size:11px;">{{ trans('messages.woo.col_promo_score') }}</span>
									<span class="fw-600 stat-num" style="font-size:14px;">
										@php $score = $product->getPromotabilityScore(); @endphp
										@if($score !== '—' && (float)$score >= 3.5)
											<span class="text-success">{{ $score }}</span>
										@elseif($score !== '—' && (float)$score >= 2.0)
											<span style="color:#ffc107;">{{ $score }}</span>
										@elseif($score !== '—')
											<span class="text-danger">{{ $score }}</span>
										@else
											{{ $score }}
										@endif
									</span>
								</div>
								<div class="col-6 text-end">
									<span class="d-block text-muted" style="font-size:11px;">{{ trans('messages.woo.col_margin') }}</span>
									<span class="fw-600 stat-num" style="font-size:14px;">{{ $product->getProfitMargin() }}</span>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		@endforeach
	</div>
	
	@include('elements/_per_page_select', ["items" => $products])
		
@elseif (!empty(request()->keyword))
	<div class="empty-list">
		<span class="material-symbols-rounded">category</span>
		<span class="line-1">
			{{ trans('messages.no_search_result') }}
		</span>
	</div>
@else
	<div class="empty-list">
		<span class="material-symbols-rounded">category</span>
		<span class="line-1 text-muted">
			<p>{!! trans('messages.product.no_product') !!}</p>
		</span>
	</div>
@endif
