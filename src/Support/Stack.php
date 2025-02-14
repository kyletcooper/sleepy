<?php

namespace WRD\Sleepy\Support;

/**
 * @template T
 */
class Stack {
    /**
     * @var T[]
     */
    protected array $stack = [];

    public function push( $item ): self {
        array_unshift( $this->stack, $item );
		
		return $this;
    }

    public function pop() {
        return array_shift( $this->stack );
    }

    /**
     * @return T
     */
    public function top() {
        return current( $this->stack );
    }

    /**
     * @return \Illuminate\Support\Collection<string, T>
     */
    public function values(){
        return collect( $this->stack );
    }

    public function isEmpty() {
        return empty( $this->stack );
    }

    public function count() {
        return count( $this->stack );
    }
}