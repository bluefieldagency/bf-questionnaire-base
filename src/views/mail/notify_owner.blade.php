@extends('questionnaire::mail.layout')

@section('content')

    <table role="presentation" aria-hidden="true" cellspacing="0" cellpadding="0" border="0" align="center" class="document" width="100%">
        <tr>
            <td valign="top">
                <table role="presentation" aria-hidden="true" cellspacing="0" cellpadding="0" border="0" align="center" width="100%" class="container">
                    <tr>
                        <td bgcolor="#03071a" align="center">
                            <table role="presentation" aria-hidden="true" cellspacing="0" cellpadding="0" border="0" align="center" width="100%">
                                <tr>
                                    <td width="25">&nbsp;</td>
                                    <td><img src="{{ $message->embed(public_path() . '/images/bf-logo.png') }}" alt="Blue Field Agency" width="171" height="67"></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td bgcolor="#faf8f8" align="center">
                            <table role="presentation" aria-hidden="true" cellspacing="0" cellpadding="0" border="0" align="center" width="100%">
                                <tr>
                                    <td width="25">&nbsp;</td>
                                    <td>
                                        <h2 class="aeonik30 title">Beste admin,</h2>
                                        <p class="aeonik22">
                                            Zie de ingevulde gegevens van de {{ $questionnaire->title }}:
                                        </p>

                                        <table role="presentation" aria-hidden="true" cellspacing="0" cellpadding="0" border="0" align="center" width="100%">
                                            @foreach($result as $pageIterator => $page)
                                                @foreach($page as $line)
                                                    <tr>
                                                        <td valign="top" style="padding-right: 30px;">{{ $line['question']->title }}:</td>
                                                        <td valign="top">{{ $line['answer'] }}</td>
                                                    </tr>
                                                @endforeach

                                                <tr>
                                                    <td>&nbsp;</td>
                                                    <td>&nbsp;</td>
                                                </tr>
                                            @endforeach
                                        </table>
                                    </td>
                                    <td width="25">&nbsp;</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td bgcolor="#03071a" align="center" class="dark-mode footer">
                            <table role="presentation" aria-hidden="true" cellspacing="0" cellpadding="0" border="0" align="center" width="100%">
                                <tr>
                                    <td width="25">&nbsp;</td>
                                    <td><img src="{{ $message->embed(public_path() . '/images/bf-logo.png') }}" alt="Blue Field Agency" width="171" height="67"></td>
                                    <td class="aeonik9 copyright">&copy; Copyright {{ date('Y') }} Blue Field Agency. All rights reserved.</td>
                                    <td width="25">&nbsp;</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

@endsection