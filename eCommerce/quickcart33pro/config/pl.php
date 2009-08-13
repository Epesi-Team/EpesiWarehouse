<?php
setlocale( LC_CTYPE, 'pl_PL' );

require_once 'epesi_pl.php';

$aMenuTypes[1] = 'Menu górne nad logo';
$aMenuTypes[2] = 'Menu górne pod logo';
$aMenuTypes[3] = 'Kategorie';
$aMenuTypes[4] = 'Producenci';
$aMenuTypes[5] = 'Ukryte menu';

$aPay[1][0] = 'Karta płatnicza';
$aPay[1][1] = 'mTransfer (mBank)';
$aPay[1][2] = 'Płacę z Inteligo (PKO BP Inteligo)';
$aPay[1][3] = 'Multitransfer (MultiBank)';
$aPay[1][4] = 'DotPay Transfer (DotPay.pl)';
$aPay[1][6] = 'Przelew24 (Bank Zachodni WBK)';
$aPay[1][7] = 'ING OnLine (ING Bank Śląski)';
$aPay[1][8] = 'Sez@m (Bank Przemysłowo-Handlowy S.A.)';
$aPay[1][9] = 'Pekao24 (Bank Pekao S.A.)';
$aPay[1][10] = 'MilleNet (Millennium Bank)';
$aPay[1][12] = 'Serwis PayPal';
$aPay[1][13] = 'Deutsche Bank PBC S.A.';
$aPay[1][14] = 'Kredyt Bank S.A. - KB24 Bankowość Elektroniczna';
$aPay[1][15] = 'PKO BP (konto Inteligo)';
$aPay[1][16] = 'Lukas Bank';
$aPay[1][17] = 'Nordea Bank Polska';
$aPay[1][18] = 'Bank BPH (usługa Przelew z BPH)';
$aPay[1][19] = 'Citibank Handlowy';
$aPay[4]['m'] = 'mTransfer - mBank';
$aPay[4]['n'] = 'MultiTransfer - MultiBank';
$aPay[4]['w'] = 'BZWBK - Przelew24';
$aPay[4]['o'] = 'Pekao24Przelew - BankPekao';
$aPay[4]['i'] = 'Płace z Inteligo';
$aPay[4]['d'] = 'Płac z Nordea';
$aPay[4]['p'] = 'Płac z PKO BP';
$aPay[4]['h'] = 'Płac z BPH';
$aPay[4]['g'] = 'Płac z ING';
$aPay[4]['c'] = 'Karta kredytowa';
?>