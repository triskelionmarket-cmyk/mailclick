<?php

namespace Acelle\Model;

use Illuminate\Database\Eloquent\Model;
use Acelle\Library\Traits\HasUid;

class Product extends Model
{
    use HasUid;

    public const STATUS_ACTIVE = 'active';  // đang hoạt động
    public const STATUS_INACTIVE = 'inactive'; //  Không hoạt động
    public const STATUS_INPROGRESS = 'inprogress'; // Chờ duyệt
    public const STATUS_DRAPP = 'drap'; // bản nháp
    public const STATUS_WARNING = 'warning'; // Vi phạm
    public const STATUS_REMOVE = 'remove'; // Đã xóa

    public static $itemsPerPage = 16;

    // paths
    public const PATH_BASE = 'products';
    public const PATH_IMAGES = 'images';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'category_id', 'title', 'content', 'customer_id', 'source_id', 
        'source_item_id', 'name', 'price', 'description', 'status', 
        'stock', 'curency', 'sku', 'unit', 'file', 'pack'
    ];

    // belongs to customer
    public function customer()
    {
        return $this->belongsTo('Acelle\Model\Customer');
    }

    // belongs to source

    public function source()
    {
        return $this->belongsTo('Acelle\Model\Source');
    }

    /**
     * Get linked WooProduct by source_item_id = woo_product_id.
     */
    public function wooProduct()
    {
        return $this->hasOne(\Acelle\Model\WooProduct::class, 'woo_product_id', 'source_item_id');
    }

    /**
     * Get category label from linked WooProduct or own category.
     */
    public function getCategoryLabel()
    {
        // Try WooProduct categories_json first
        $wp = $this->wooProduct;
        if ($wp && $wp->categories_json) {
            $cats = is_array($wp->categories_json) ? $wp->categories_json : json_decode($wp->categories_json, true);
            if (is_array($cats) && !empty($cats)) {
                return is_string($cats[0]) ? $cats[0] : ($cats[0]['name'] ?? trans('messages.woo.uncategorized'));
            }
        }

        // Try own category
        $cat = $this->smsCategory;
        if ($cat) {
            return $cat->name;
        }

        return trans('messages.woo.uncategorized');
    }

    /**
     * Get promotability score (RFM from WooProduct).
     */
    public function getPromotabilityScore()
    {
        $wp = $this->wooProduct;
        return $wp ? number_format($wp->rfm_score, 1) : '—';
    }

    /**
     * Get profit margin from linked WooProduct.
     */
    public function getProfitMargin()
    {
        $wp = $this->wooProduct;
        return $wp ? $wp->profit_margin . '%' : '—';
    }

    public function productAttributes()
    {
        return $this->hasMany('Acelle\Model\ProductAttribute');
    }

    public function scopeFilter($query, $attribute, $value)
    {
        $query->where($attribute, '=', $value);
    }

    public function getBasePath($path = null)
    {
        $path = join_paths($this->customer->getBasePath(), self::PATH_BASE, $this->uid); // storage/app/products/000000/

        if (!\Illuminate\Support\Facades\File::exists($path)) {
            \Illuminate\Support\Facades\File::makeDirectory($path, 0777, true, true);
        }

        return $path;
    }

    public function getImagesPath($path = null)
    {
        $path = join_paths($this->getBasePath(), self::PATH_IMAGES, $this->uid); // storage/app/products/000000/

        if (!\Illuminate\Support\Facades\File::exists($path)) {
            \Illuminate\Support\Facades\File::makeDirectory($path, 0777, true, true);
        }

        return $path;
    }

    public function getImagePaths()
    {
        $path = $this->getImagesPath();
        $files = array_diff(scandir($path), array('.', '..'));
        $paths = [];

        foreach ($files as $filename) {
            $paths[] = join_paths($this->getImagesPath(), $filename);
        }

        return $paths;
    }

    public function removeImageByUrl($url)
    {
        $paths = $this->getImagePaths();

        foreach ($paths as $path) {
            if (\Acelle\Helpers\generatePublicPath($path) == $url) {
                \Illuminate\Support\Facades\File::delete($path);
            }
        }
    }

    public function getImageUrls()
    {
        $paths = $this->getImagePaths();
        $urls = [];

        foreach ($paths as $path) {
            $urls[] = \Acelle\Helpers\generatePublicPath($path);
        }

        return $urls;
    }

    public function getImageUrl()
    {
        // First check if linked WooProduct has an external image URL
        $wp = $this->wooProduct;
        if ($wp && $wp->images_json) {
            $images = is_array($wp->images_json) ? $wp->images_json : json_decode($wp->images_json, true);
            if (is_array($images) && !empty($images)) {
                $first = $images[0];
                // Could be a plain URL string or an object with 'src' key
                $url = is_string($first) ? $first : ($first['src'] ?? null);
                if ($url) {
                    return $url;
                }
            }
        }

        // Fallback to local uploaded images
        $urls = $this->getImageUrls();

        return empty($urls) ? url('images/no-product-image.png') : $urls[0];
    }

    public static function generateWidgetProductListHtmlContent($params)
    {
        $products = Product::limit($params['count']);
        $sort = explode('-', $params['sort']);

        if (!isset($sort[1]) || !isset($params['count']) || !isset($params['cols'])) {
            return "";
        }

        $products = $products->orderBy(explode('-', $params['sort'])[0], explode('-', $params['sort'])[1]);
        $products = $products->get();

        return view('products.widgetProductListHtmlContent', [
            'products' => $products,
            'options' => $params,
        ]);
    }

    public static function generateWidgetProductHtmlContent($params)
    {
        $product = self::findByUid($params['id']);

        // replace tags
        $html = $params['content'];
        $html = str_replace('*|PRODUCT_NAME|*', $product->title, $html);
        $html = str_replace('*|PRODUCT_DESCRIPTION|*', substr(strip_tags($product->description), 0, 200), $html);
        $html = str_replace('*|PRODUCT_PRICE|*', format_price($product->price), $html);
        $html = str_replace('*|PRODUCT_QUANTITY|*', $product->title, $html);
        $html = str_replace('*|PRODUCT_URL|*', action('ProductController@index'), $html);
        $html = str_replace('*%7CPRODUCT_URL%7C*', action('ProductController@index'), $html);

        // try to replace product image
        $dom = new \DOMDocument();
        $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'), LIBXML_NOWARNING | LIBXML_NOERROR | LIBXML_HTML_NODEFDTD);

        $imgs = $dom->getElementsByTagName("img");
        foreach ($imgs as $img) {
            $att = $img->getAttribute('builder-element');
            if ($att == 'ProductImgElement') {
                $img->setAttribute('src', $product->getImageUrl());
            }
        }

        return $dom->saveHTML();
    }

    public static function newDefault()
    {
        $product = new self();
        $product->status = self::STATUS_DRAPP;
        $product->uid =  uniqid();
        return $product;
    }

    public static function scopeSearch($query, $keyword)
    {
        if ($keyword) {
            $query =  $query->where('name', 'like', '%'.$keyword.'%');
        }
    }

    public function smsCategory()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function fillParams($params)
    {
        $this->title = $params['title'] ?? '';
        $this->content = $params['content'] ?? '';

        // fill category
        if (isset($params['category_uid'])) {
            $this->category_id = Category::findByUid($params['category_uid'])->id;
        }
    }

    public static function getTags()
    {
        return [
            'first_name',
            'phone',
            'last_name',
            'email',
            'username',
            'company',
            'address',
            'birth_date',
            'anniversary_date',
            'state',
            'event_date',
            'website'
        ];
    }

    public function saveFromParams($params)
    {
        // fill
        $this->fillParams($params);

        // validation
        $validator = \Validator::make($params, [
            'title'   => ['required'],
            'category_uid'   => ['required'],
        ]);

        // check if has errors
        if ($validator->fails()) {
            return $validator;
        }

        // save to db
        $this->save();

        // save attributes values
        $this->productAttributes()->delete();
        if ($params['product_attributes']) {
            foreach ($params['product_attributes'] as $attributeUid => $value) {
                if ($value) {
                    $this->setValueByAttribute(Attribute::findByUid($attributeUid), $value);
                }
            }
        }

        // upload images
        if (isset($params['images'])) {
            foreach ($params['images'] as $file) {
                $this->uploadImage($file);
            }
        }

        // remove images
        if (isset($params['delete_images'])) {
            foreach ($params['delete_images'] as $url) {
                $this->removeImageByUrl($url);
            }
        }

        // return false
        return $validator;
    }

    public function uploadImage($file)
    {
        // Afișează URL-ul pentru debugging
        //exit($file);  // Acesta este URL-ul imaginii care vine din feed-ul WooCommerce
    
        // Obține conținutul imaginii de la URL
        $imageContent = file_get_contents($file);
    
        // Verifică dacă am reușit să obținem imaginea
        if ($imageContent === false) {
            throw new \Exception("Nu am putut descărca imaginea de la URL.");
        }
    
        // Creează un fișier temporar pentru imagine
        $tempFilePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('image_') . '.png';
    
        // Scrie conținutul imaginii în fișierul temporar
        file_put_contents($tempFilePath, $imageContent);
    
        // Încarcă fișierul temporar ca un obiect UploadedFile în Laravel
        $tempFile = new \Illuminate\Http\UploadedFile($tempFilePath, 'image.png', null, null, true);
    
        // Creează un ID unic pentru imagine
        $imageId = uniqid();
    
        // Mută fișierul în locația dorită folosind move()
        $tempFile->move($this->getImagesPath(), $imageId . '.png');
    
        // Șterge fișierul temporar după mutare
        unlink($tempFilePath);
    
        return $imageId;  // Poți returna ID-ul imaginii sau calea completă
    }
    

    public function setValueByAttribute($attribute, $value)
    {
        $exist = $this->productAttributes()
            ->where('attribute_id', $attribute->id)
            ->first();

        if ($exist) {
            $exist->value = $value;
            $exist->save();
        } else {
            ProductAttribute::create([
                'attribute_id' => $attribute->id,
                'product_id' => $this->id,
                'value' => $value,
            ]);
        }
    }

    public function getValueByAttribute($attribute)
    {
        $av = $this->productAttributes()
            ->where('attribute_id', $attribute->id)
            ->first();

        return $av ? $av->value : null;
    }
}
