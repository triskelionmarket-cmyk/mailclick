@foreach($products as $product)
    <div class="col-{{ 12/$options['cols'] }}">
        <div class="">
            <div class="img-col mb-3">
                <div class="d-flex align-items-center justify-content-center" style="">
                    <a style="width:100%" href="#" class="mr-4">
                        <img width="100%" src="{{ $product->getImageUrl() }}" style="max-width:100%;" />
                    </a>
                </div>
            </div>
            <div class="">
                <p class="product-name mb-1">
                    <a style="color: #333;font-weight:600" href="#" class="mr-4">{{ $product->title }}</a>
                </p>
                <p class=" product-description">{{ substr(strip_tags($product->description), 0, 100) }}</p>
                <p><strong>{{ format_price($product->price) }}</strong></p>
                <a href="#" style="background-color: #9b5c8f;
    border-color: #9b5c8f;" class="btn btn-primary text-white">
                    {{ trans('messages.automation.buy_now') }}
                </a>
            </div>
        </div>
    </div>
@endforeach


<div id="products-list-widget" class="row"></div>

<script>
fetch("/products/widget/products/list")
  .then(response => response.json())
  .then(products => {
    const container = document.getElementById('products-list-widget');
    if (!products.length) {
      container.innerHTML = "<p>Nu există produse disponibile.</p>";
      return;
    }

    products.forEach(product => {
      const col = document.createElement('div');
      col.className = "col-{{ 12/$options['cols'] }}";

      col.innerHTML = `
        <div>
          <div class="img-col mb-3">
            <div class="d-flex align-items-center justify-content-center">
              <a style="width:100%" href="#" class="mr-4">
                <img width="100%" src="${product.image || ''}" style="max-width:100%" />
              </a>
            </div>
          </div>
          <div>
            <p class="product-name mb-1">
              <a style="color: #333;font-weight:600" href="#" class="mr-4">${product.title}</a>
            </p>
            <p class="product-description">${product.sku || ''}</p>
            <p><strong>${product.price} lei</strong></p>
            <a href="#" style="background-color: #9b5cf8; border-color: #9b5cf8;" class="btn btn-primary text-white">
              Cumpără acum
            </a>
          </div>
        </div>
      `;

      container.appendChild(col);
    });
  });
</script>
