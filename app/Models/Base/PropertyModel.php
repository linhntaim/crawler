<?php

/**
 * Base - Any modification needs to be approved, except the space inside the block of TODO
 */

namespace App\Models\Base;

use App\ModelCasts\SelfCast;
use App\ModelResources\Base\PropertyResource;
use App\ModelTraits\MultipleKeyTrait;
use App\ModelTraits\SelfCasterTrait;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

/**
 * Class PropertyModel
 * @package App\Models\Base
 * @property string $name
 * @property mixed $value
 */
abstract class PropertyModel extends Model implements ISelfCaster
{
    use SelfCasterTrait, MultipleKeyTrait;

    public $incrementing = false;

    public $timestamps = false;

    protected $visible = [
        'name',
    ];

    protected $resourceClass = PropertyResource::class;

    public static function nullInstance(string $name)
    {
        $model = new static();
        $model->name = $name;
        return $model;
    }

    public function getKeyName()
    {
        return [
            $this->getHasPropertyModelForeignKey(),
            'name',
        ];
    }

    public function applyValueCaster()
    {
        $caster = $this->getPropertyDefinition()->getCaster($this->name);
        if ($caster instanceof CastsAttributes) {
            $this->casts['value'] = SelfCast::class;
            $this->setCaster('value', $caster);
        }
        elseif (is_string($caster)) {
            $this->casts['value'] = $caster;
        }
        return $this;
    }

    public function applyCasters()
    {
        return $this->hasCaster('value') ? $this : $this->applyValueCaster();
    }

    /**
     * @return PropertyDefinition
     */
    public function getPropertyDefinition()
    {
        return $this->getNewHasPropertyModel()->getPropertyDefinition();
    }

    /**
     * @return IHasProperties
     */
    public function getNewHasPropertyModel()
    {
        $modelClass = $this->getHasPropertyModelClass();
        return new $modelClass();
    }

    public abstract function getHasPropertyModelClass();

    public abstract function getHasPropertyModelForeignKey();

    public function hasPropertyModel()
    {
        return $this->belongsTo($this->getHasPropertyModelClass(), $this->getHasPropertyModelForeignKey());
    }
}
