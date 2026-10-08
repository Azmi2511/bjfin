@extends('errors.layout')

@php
    $code = 429;
    $icon = '⏱';
    $title = 'Terlalu Banyak Permintaan';
    $message = 'Permintaan Anda terlalu banyak dalam waktu singkat. Silakan tunggu beberapa saat sebelum mencoba lagi.';
@endphp