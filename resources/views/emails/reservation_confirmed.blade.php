@extends('templates.master')

@section('content')
    <h1>Olá, {{ $reserva['name'] }}!</h1>

    <p>Sua reserva foi confirmada!</p>
    <p>Dia: <strong>{{ \Carbon\Carbon::parse($reserva['data'])->format('d/m/Y') }}</strong></p>
    <p>Hora: <strong>{{ \Carbon\Carbon::parse($reserva['hora'])->format('H:i') }}</strong></p>
    <p>Quantidade de pessoas: <strong>{{ $reserva['quantidade_pessoas'] }}</strong></p>

    <br>

    <p>Obrigado por reservar conosco!</p>
@endsection
