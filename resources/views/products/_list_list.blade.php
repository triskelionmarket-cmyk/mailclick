@if ($products->count() > 0)
	<table class="table table-box pml-table mt-2"
		current-page="{{ empty(request()->page) ? 1 : empty(request()->page) }}"
	>
		@foreach ($products as $key => $product)
			<tr>
				<td width="1%">
					<div class="product-image-list mr-3">
						<img src="{{ $product->getImageUrl() }}" />
					</div>
				</td>
				<td width="30%">
					<h5 class="no-margin text-normal">
						<span class="kq_search" href="javascript:;">
							{{ $product->title }}
						</span>
					</h5>
					<span class="text-muted d-block mt-1" style="font-size:12px;">
						<span class="material-symbols-rounded me-1" style="font-size:13px;">category</span>
						{{ $product->getCategoryLabel() }}
					</span>
					<span class="text-muted d-block mt-1">
						{{ trans('messages.created_at') }}:
						{{ Auth::user()->customer->formatDateTime($product->created_at, 'datetime_full') }}
					</span>
				</td>
				<td>
					<h5 class="no-margin stat-num">{{ number_format($product->price, 0) }} RON</h5>
					<span class="text-muted d-block mt-2">{{ trans('messages.woo.col_price') }}</span>
				</td>
				<td>
					<h5 class="no-margin stat-num">
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
					</h5>
					<span class="text-muted d-block mt-2">{{ trans('messages.woo.col_promo_score') }}</span>
				</td>
				<td>
					<h5 class="no-margin stat-num">{{ $product->getProfitMargin() }}</h5>
					<span class="text-muted d-block mt-2">{{ trans('messages.woo.col_margin') }}</span>
				</td>
				<td>
					<h5 class="no-margin stat-num">
						{{ $product->source ? $product->source->getName() : '—' }}
					</h5>
					<span class="text-muted d-block mt-2">{{ trans('messages.source') }}</span>
				</td>
				<td class="text-end">
					<a href="{{ action('CampaignController@selectType') }}"
						role="button" class="btn btn-secondary m-icon pl-3">
						<span class="material-symbols-rounded me-1">campaign</span>{{ trans('messages.woo.btn_promote') }}</a>
					<div class="btn-group">
						<button role="button" class="btn btn-light dropdown-toggle" data-bs-toggle="dropdown"></button>
						<ul class="dropdown-menu dropdown-menu-end">
							<li>
								<a
									class="dropdown-item list-action-single"
									link-confirm="{{ trans('messages.source.delete.confirm') }}"
									link-method="POST"
									href="{{ action('SourceController@delete', ['uids' => $product->uid]) }}">
									<span class="material-symbols-rounded">delete_outline</span> {{ trans('messages.delete') }}
								</a>
							</li>
						</ul>
					</div>
				</td>
			</tr>
		@endforeach
	</table>
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
