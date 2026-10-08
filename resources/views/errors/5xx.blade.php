@extends('errors.layout')

@php
    $code = $exception->getStatusCode() ?? 500;
    $icon = '!';
    $title = 'Terjadi Kesalahan Server';
    $message = 'Server mengalami masalah saat memproses permintaan Anda. Silakan coba kembali nanti.';
@endphp