<?php

function array_every(array $array, callable $condition)
{
    return array_reduce($array, function ($accum, $item) use ($condition) {
        $check = call_user_func($condition, $item);
        if ($accum === false) return false;

        return $accum && $check;
    }, true);
}
