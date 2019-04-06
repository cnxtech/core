<?php


namespace Codex\Mergable;


use Illuminate\Support\Arr;
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;

class ParameterBag extends \Symfony\Component\DependencyInjection\ParameterBag\ParameterBag
{
    /** @var \Codex\Contracts\Mergable\Model */
    protected $model;

    /**
     * {@inheritdoc}
     */
    public function get($name)
    {
        $name = (string)$name;

        if ( ! \array_key_exists($name, $this->parameters)) {
            if ( ! $name) {
                throw new ParameterNotFoundException($name);
            }

            if($this->hasModel() && $this->model->hasAttribute($name)){
                return $this->model->getRawAttributeValue($name);
            }

            $alternatives = [];
            foreach ($this->parameters as $key => $parameterValue) {
                $lev = levenshtein($name, $key);
                if ($lev <= \strlen($name) / 3 || false !== strpos($key, $name)) {
                    $alternatives[] = $key;
                }
            }

            $nonNestedAlternative = null;
            if ( ! \count($alternatives) && false !== strpos($name, '.')) {
                $namePartsLength = array_map('strlen', explode('.', $name));
                $key             = substr($name, 0, -1 * (1 + array_pop($namePartsLength)));
                while (\count($namePartsLength)) {
                    if ($this->has($key)) {
                        if (\is_array($this->get($key))) {
                            $nonNestedAlternative = $key;
                        } elseif (Arr::accessible($this->get($key))) {
                            $subKey = implode('.', array_slice(explode('.', $name), 1));
                            $item   = $this->get($key);
                            return $item[ $subKey ];
                        }
                        break;
                    }

                    $key = substr($key, 0, -1 * (1 + array_pop($namePartsLength)));
                }
            }

            throw new ParameterNotFoundException($name, null, null, null, $alternatives, $nonNestedAlternative);
        }

        return $this->parameters[ $name ];
    }

    /**
     * {@inheritdoc}
     */
    public function has($name)
    {
        if(array_key_exists((string) $name, $this->parameters)){
            return true;
        }
        if($this->hasModel() && $this->model->hasAttribute($name)){
            return true;
        }
        return false;
    }

    /**
     * @return \Codex\Contracts\Mergable\Model
     */
    public function getModel(): \Codex\Contracts\Mergable\Model
    {
        return $this->model;
    }

    /**
     * @param \Codex\Contracts\Mergable\Model $model
     */
    public function setModel(\Codex\Contracts\Mergable\Model $model): void
    {
        $this->model = $model;
    }

    public function hasModel()
    {
        return $this->model !== null;
    }

//    public function resolveValue($value, array $resolving = [])
//    {
//        $value = parent::resolveValue($value, $resolving);
//        if($value instanceof Closure){
//            $value=$value();
//
//        }
//        return $value;
//    }


//    /**
//     * wire $this->set() logic into add() too
//     *
//     * @param array $parameters
//     */
//    public function add( array $parameters )
//    {
//        foreach ( $parameters as $name => $value ) {
//            $this->set( $name, $value );
//        }
//    }
//
//    /**
//     * sets all levels of nested array parameters with dot notation
//     * - loggly[host: loggly.com] will be translated this way:
//     *  - loggly: [host: loggly.com] - standard array parameter will be left as is
//     *  - loggly.host: loggly.com - nested variables ar translated so you can access them directly too as parent.variable
//     *
//     * @param string $name
//     * @param mixed $value
//     */
//    public function set( $name, $value )
//    {
//        if ( $this->has( $name ) ) {
//            // this is required because of array values
//            // we can have arrays defined there, so we need to remove them first
//            // otherwise some subvalues would to remain in the system and as a result, arrays would be merged, not overwriten by set()
//            $this->remove( $name );
//        }
//        $this->setNested( $name, $value );
//    }
//
//    /**
//     * remove checks even if name is not array
//     *
//     * @param string $name
//     */
//    public function remove( $name )
//    {
//        $value = $this->get( $name );
//        if ( is_array( $value ) ) {
//            foreach ( $value as $k => $v ) {
//                $this->remove( $name . '.' . $k, $v );
//            }
//        }
//        if ( strpos( $name, '.' ) !== FALSE ) {
//            $parts = explode( '.', $name );
//            $nameTopLevel = reset( $parts );
//            array_shift( $parts );
//            $topLevelData = $this->removeKeyByAddress( $this->get( $nameTopLevel ), $parts );
//            ksort( $topLevelData );
//            $this->setNested( $nameTopLevel, $topLevelData );
//        }
//        parent::remove( $name );
//    }
//
//    /**
//     * @param array $data
//     * @param array $addressParts
//     *
//     * @return array
//     */
//    private function removeKeyByAddress( $data, $addressParts )
//    {
//        $updatedLevel = & $data;
//        $i = 1;
//        foreach ( $addressParts as $part ) {
//            if ( $i === count( $addressParts ) ) {
//                unset( $updatedLevel[$part] );
//            } else {
//                $updatedLevel = & $updatedLevel[$part];
//                $i++;
//            }
//        }
//        return $data;
//    }
//
//    /**
//     * @see set()
//     *
//     * @param string $name
//     * @param mixed $value
//     */
//    private function setNested( $name, $value )
//    {
//        if ( is_array( $value ) ) {
//            foreach ( $value as $k => $v ) {
//                $this->setNested( $name . '.' . $k, $v );
//            }
//        }
//        parent::set( $name, $value );
//    }


}
