<?php


namespace Codex\Mergable\Concerns;

trait HasRelations
{

    /**
     * The loaded relationships for the model.
     *
     * @var array
     */
    protected $relations = [];

    /**
     * The relationships that should be touched on save.
     *
     * @var array
     */
    protected $touches = [];


    /**
     * Determine if the model touches a given relation.
     *
     * @param  string $relation
     *
     * @return bool
     */
    public function touches($relation)
    {
        return in_array($relation, $this->touches);
    }

    /**
     * Get all the loaded relations for the instance.
     *
     * @return array
     */
    public function getRelations()
    {
        return $this->relations;
    }

    /**
     * Get a specified relationship.
     *
     * @param  string $relation
     *
     * @return mixed
     */
    public function getRelation($relation)
    {
        return $this->relations[ $relation ];
    }

    /**
     * Determine if the given relation is loaded.
     *
     * @param  string $key
     *
     * @return bool
     */
    public function relationLoaded($key)
    {
        return array_key_exists($key, $this->relations);
    }

    /**
     * Set the given relationship on the model.
     *
     * @param  string $relation
     * @param  mixed  $value
     *
     * @return $this
     */
    public function setRelation($relation, $value)
    {
        $this->relations[ $relation ] = $value;

        return $this;
    }

    /**
     * Unset a loaded relationship.
     *
     * @param  string $relation
     *
     * @return $this
     */
    public function unsetRelation($relation)
    {
        unset($this->relations[ $relation ]);

        return $this;
    }

    /**
     * Set the entire relations array on the model.
     *
     * @param  array $relations
     *
     * @return $this
     */
    public function setRelations(array $relations)
    {
        $this->relations = $relations;

        return $this;
    }

    /**
     * Get the relationships that are touched on save.
     *
     * @return array
     */
    public function getTouchedRelations()
    {
        return $this->touches;
    }

    /**
     * Set the relationships that are touched on save.
     *
     * @param  array $touches
     *
     * @return $this
     */
    public function setTouchedRelations(array $touches)
    {
        $this->touches = $touches;

        return $this;
    }
}
