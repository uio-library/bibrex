@extends('layouts.master')

@section('content')


<div class="card mb-3">
    <div class="card-header">
        <h5>Korleis fungerar Bibrex?</h5>
    </div>
    <div class="card-body">
        <ul>
            <li>
                Bibrex inneholder ei samling av <em>ting</em>, og for kvar ting kan det finnast eit eller fleire <em>eksemplar</em>.
            </li>
            <li>
                Tinga delast for alle bibliotek, men kvart bibliotek kan velje hvilke dei vil aktivere.
            </li>
            <li>
                Når du registrerer eit nytt eksemplar blir du bedt om å legge inn ein unik ID for eksemplaret.
                Denne kan du ta frå ei strekkode eller dikte opp sjølv og registrere i ei RFID-brikke.
                Bibrex bryr seg kun om at den er unik – og vil bjeffe på deg om han ikkje er det.
            </li>
        </ul>
    </div>
</div>


<div class="card mb-3">
    <div class="card-header">
        <h5>Lånetid og påminningar?</h5>
    </div>
    <div class="card-body">
        <ul>
            <li>
                Standard lånetid kan setjast for kvar ting, men merk at lånetida er felles for alle bibliotek!
            </li>
            <li>
                Lånetida <em>rundast ned</em> til næraste natt.
                Ei lånetid på 1 dag inneber dermed at tingen kan lånast ut dagen.
            </li>
            <li>
                Neste morgon blir første påminning sendt.
            </li>
            <li>
                Det blir ikkje sendt fleire påminningar automatisk, så blir ikkje tingen levert må ein følje opp manuelt
                (Kan hende blir dette endra i framtida).
            </li>
            <li>
                Du kan sjå om det har blitt sendt påminningar i utlånsoversikta.
                Du kan òg trykkje på ei påminning for å sjå sjølve meldinga som blei sendt.
            </li>
        </ul>
    </div>
</div>


<div class="card mb-3">
    <div class="card-header">
        <h5>Tilgang</h5>
    </div>
    <div class="card-body">
        <ul>
            <li>
                Kvart bibliotek har si eige pålogging.
            </li>
            <li>
                Pålogging kan gjerast med brukarnamn eller automatisk basert på IP-adresse.
            </li>
            <li>
                Kvart bibliotek sette sjølv opp <a href="/my/ips">hvilke IP-adressar</a> som skal loggast på automatisk.
            </li>
            <li>
                Kven som helst kan opprette og slette ting
                (men det fort gjort å gjenopprette ei ting om ho blir feilaktig sletta).
            </li>
        </ul>
    </div>
</div>


<div class="card mb-3">
    <div class="card-header">
        <h5>Kva med personvern?</h5>
    </div>
    <div class="card-body">
        <ul>
            <li>
                Fyrste gong ein brukar låner noko blir fylgjande importert frå Alma:
                <ul>
                    <li>
                        Låne-ID og Feide-ID for å kunne identifisere brukaren. Låne-ID trengs for å kunne scanne lånekortet,
                        medan Feide-ID trengs som stabil identifikator hvis brukaren får nytt kort.
                    </li>
                    <li>
                        Fornavn, etternavn, gruppe («egne ansatte», «egne studenter») for å kunne identifisere brukaren
                        og skilje hen frå andre brukare i søket.
                    </li>
                    <li>
                        Føretrukket språk for å kunne sende påminningar på dette språket.
                    </li>
                    <li>
                        Epost og telefon for å kunne sende påminningar.
                    </li>
                </ul>
            </li>
            <li>
                Brukare importert frå Alma blir sletta eit år etter dei sist lånte noko.
            </li>
            <li>
                Manuelt oppretta brukare blir sletta fem år etter dei sist lånte noko.
            </li>
            <li>
                Lån anonymiserast fyrste natt etter tingen har blitt levert.
            </li>
            <li>
                For statistikkføremål lagrar vi kor mange lån kvar brukar har gjort (men ikkje kva eller når).
            </li>
        </ul>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">
        <h5>Kva gjer eg om eg får ei feilmelding?</h5>
    </div>
    <div class="card-body">
        <ul>
            <li>
                Kontakt Dan Michael eller bibrex-diskusjon@ub.uio.no .
            </li>
        </ul>
    </div>
</div>


@stop
