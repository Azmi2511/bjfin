@extends('errors.layout')

@php
    $code = $exception->getStatusCode() ?? 400;
    $icon = '!';
    $title = 'Permintaan Tidak Dapat Diproses';
    $message = 'Permintaan Anda tidak dapat diproses oleh sistem.';
@endphp