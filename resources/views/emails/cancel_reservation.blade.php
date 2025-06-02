@extends('templates.master')

@section('title', 'Cancelamento de Reserva')

@section('content')
    <table width="100%" cellpadding="0" cellspacing="0" bgcolor="beige">
        <tr>
            <td align="center">
                <div class="container">
                    <h1>Olá, {{ $reserva['name'] }}!</h1>

                    <p>Sua reserva foi cancelada!</p>
                    <p>Dia: <strong>{{ \Carbon\Carbon::parse($reserva['data'])->format('d/m/Y') }}</strong></p>
                    <p>Hora: <strong>{{ \Carbon\Carbon::parse($reserva['hora'])->format('H:i') }}</strong></p>
                    <p>Quantidade de pessoas: <strong>{{ $reserva['quantidade_pessoas'] }}</strong></p>

                    @if(isset($reserva['motivo_cancelamento']))
                        <p><strong>Motivo do cancelamento:</strong> {{ $reserva['motivo_cancelamento'] }}</p>
                    @endif
                </div>
            </td>
        </tr>
    </table>
@endsection
