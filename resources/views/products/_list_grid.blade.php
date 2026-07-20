@if ($products->count() > 0)
	<div class="row mt-4">
		@foreach ($products as $key => $product)
			<div class="col-md-3 col-sm-6 col-lg-3 mb-4">
				<div class="card mb-4 box-shadow">
					<span class="product-image-box">
						<img class="card-img-top" src="{{ $product->getImageUrl() }}" style="height: 100%; width: auto; display: block;">
					</span>
					<div class="card-body p-3">
						<h5 title="{{ $product->title }}" class="fw-600 mt-1 mb-2 xtooltip" style="display: -webkit-box;
							-webkit-box-orient: vertical;
							-webkit-line-clamp: 2;
							overflow: hidden;
							text-overflow: ellipsis;">{{ $product->title }}</h5>
						<p style="display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 2;
    overflow: hidden;
    text-overflow: ellipsis;" class="card-text">
  
</p>
						<div class = "d-none">
							<div class="d-flex align-items-center">
								<button role="button" class="btn btn-secondary">{{ trans('messages.view') }}</button>
								<a
									link-confirm="{{ trans('messages.source.delete.confirm') }}"
									link-method="POST" href="{{ action('SourceController@delete', ['uids' => $product->uid]) }}"
									class="btn btn-link list-action-single">
									{{ trans('messages.delete') }}
								</a>
							</div>
						</div>
						<hr>
						<div class = "row">
							<div class="col-3 text-primary m-icon small text-start">
								<span class="text-muted">{{ $product->price }}</span>
							</div>
							<div class="col-9 text-primary m-icon small text-end">
								<img width="20px" class="mr-1 list-source-img" src="{{ url('images/' . $product->source->type . '_list.png') }}" />
								<span class="text-muted">{{ $product->source->getName() }}</span>
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
