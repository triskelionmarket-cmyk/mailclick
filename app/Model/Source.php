<?php

namespace Acelle\Model;

use Illuminate\Database\Eloquent\Model;
use Acelle\Model\Product;
use Acelle\Library\Traits\HasUid;

class Source extends Model
{
    use HasUid;

    public static $itemsPerPage = 25;
    public $service;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'sources';

    /**
     * Customer.
     *
     * @return object
     */
    public function customer()
    {
        return $this->belongsTo('Acelle\Model\Customer');
    }

    /**
     * Mail List.
     *
     * @return object
     */
    public function mailList()
    {
        return $this->belongsTo('Acelle\Model\MailList');
    }

    /**
     * Products.
     *
     * @return object
     */
    public function products()
    {
        return $this->hasMany('Acelle\Model\Product');
    }

    /**
     * Get data.
     *
     * @var object | collect
     */
    public function getData()
    {
        if (!$this['meta']) {
            return json_decode('{}', true);
        }

        return json_decode($this['meta'], true);
    }

    /**
     * Update data.
     *
     * @var object | collect
     */
    public function updateData($data)
    {
        $data = (object) array_merge((array) $this->getData(), $data);
        $this['meta'] = json_encode($data);

        $this->save();
    }

    /**
     * Search.
     *
     * @var object | collect
     */
    public function scopeSearch($query, $keyword)
    {
        // Keyword
        if (!empty(trim($keyword))) {
            foreach (explode(' ', trim($keyword)) as $k) {
                $query = $query->where(function ($q) use ($k) {
                    $q->orwhere('sources.type', 'like', '%'.strtolower($k).'%');
                });
            }
        }
    }

    /**
     * Check if source connected.
     *
     * @var object | collect
     */
    public function connected()
    {
        return isset($this->id);
    }

    /**
     * Products count.
     *
     * @return object
     */
    public function productsCount()
    {
        return $this->products()->count();
    }

    /**
     * Get display name.
     *
     * @return object
     */
    public function getName()
    {
        switch ($this->type) {
            case 'WooCommerce':
                return $this->getData()['data']['name'];
            default:
                return trans('messages.source.' . $this->type);
        }
    }

    /**
     * Get class for exist source.
     *
     * @return object
     */
    public function classMapping()
    {
        $class = '\\Acelle\\Model\\' . $this->type;
        return $class::find($this->id);
    }

    public function createDefaultList()
    {
        // list
        $list = $this->customer->newMailList();
        $list->name = trans('messages.source.list.default_name', [
            'source' => $this->getName(),
        ]);
        $list->save();

        // assign list
        $this->mail_list_id = $list->id;
        $this->save();

        return $list;
    }

    /**
     * Get source list.
     *
     * @return object
     */
    public function getList()
    {
        return $this->mailList;
    }
}
