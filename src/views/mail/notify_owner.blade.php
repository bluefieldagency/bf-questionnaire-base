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
                                            @if ($questionnaire->hasOption('admin_mail_intro') && ! empty($questionnaire->getOption('admin_mail_intro')))
                                                {!! $questionnaire->doReplacements($questionnaire->getOption('admin_mail_intro')) !!}
                                            @else
                                                Zie de ingevulde gegevens van de {{ $questionnaire->title }}:
                                            @endif
                                        </p>

                                        @if ($questionnaire->hasOption('show_fixed_data_types') && $questionnaire->getOption('show_fixed_data_types') === true)
                                            <table role="presentation" aria-hidden="true" cellspacing="0" cellpadding="0" border="0" align="center" width="100%">
                                                @foreach(\Questionnaire\Models\QuestionnaireEntry::$fixedDataTypes as $fixedDataType)
                                                    <tr>
                                                        <td valign="top" style="padding-right: 30px;">{{ $fixedDataType }}:</td>
                                                        <td valign="top">{{ $questionnaire_entry->getAttribute($fixedDataType) }}</td>
                                                    </tr>
                                                @endforeach
                                            </table>

                                            <p>&nbsp;</p>
                                        @endif

                                        @foreach($result as $pageIterator => $resultData)
                                            <p>{{ $pages[$pageIterator]->title }}</p>

                                            <table role="presentation" aria-hidden="true" cellspacing="0" cellpadding="0" border="0" align="center" width="100%">
                                                @foreach($resultData as $line)
                                                    <tr>
                                                        <td valign="top" style="padding-right: 30px;">{{ $line['question']->title }}:</td>
                                                        <td valign="top">{{ $line['answer'] }}</td>
                                                    </tr>
                                                @endforeach
                                            </table>

                                            <p>&nbsp;</p>
                                        @endforeach
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