@extends('templates.master')

@section('title', 'Confirmação de Reserva')

@section('content')
    <table width="100%" cellpadding="0" cellspacing="0" bgcolor="beige">
        <tr>
            <td align="center">
                <div class="container">
                    <h1>Olá, {{ $reserva['name'] }}!</h1>

                    <p>Obrigado por reservar conosco!</p>
                    <p>Dia: <strong>{{ \Carbon\Carbon::parse($reserva['data'])->format('d/m/Y') }}</strong></p>
                    <p>Hora: <strong>{{ \Carbon\Carbon::parse($reserva['hora'])->format('H:i') }}</strong></p>
                    <p>Quantidade de pessoas: <strong>{{ $reserva['quantidade_pessoas'] }}</strong></p>

                    <br>

                    <p>Para confirmar sua reserva, clique no botão abaixo:</p>

                    <br>

                    <p>
                        <a href="{{ $reserva['link'] }}" style="padding: 10px 15px; background-color: #28a745; color: white; text-decoration: none;">Confirmar Reserva</a>
                    </p>

                    <br>

                    <p>
                        <strong>ATENÇÃO:</strong> Após confirmar a reserva, não será mais possível fazer alterações.
                        Se precisar fazer alguma mudança, entre em contato direto com o restaurante.
                    </p>

                    <br>

                    <p>Se não foi você quem fez esta solicitação, ignore este e-mail.</p>
                </div>
            </td>
        </tr>
    </table>
@endsection
