<?php
/* @var $contract \backend\models\Contract */
/* @var $this yii\web\View */
?>

<style>
    @page {
        size: portrait;
    }
    body {
        font-size: 3mm;
        max-width: 20cm;
    }
    table td {
        text-align: justify;
    }
    table td.number, table th.number {
        width: 1cm;
        vertical-align: top;
    }
    ul {
        padding-left: 3mm;
        margin: 1mm;
    }
</style>
<div class="page">
    <?= $this->render('_header', ['contract' => $contract]); ?>

    <p style="text-align: justify;">Негосударственное образовательное учреждение «EXCLUSIVE EDUCATION» (Учебный центр «Пять с Плюсом» &trade;), осуществляющее образовательную деятельность на основании лицензии <span class="placeholder"><b>NAS UZ 001.MO.0591-05</b></span> в лице директора <span class="placeholder"><b>Климова Александра Сергеевича</b></span>, действующего на основании Устава, именуемое в дальнейшем «Учреждение», с одной стороны, и <span class="placeholder"><?= $contract->user->parent_id ? $contract->user->parent->name : $contract->user->name; ?></span>, именуемый в дальнейшем «Заказчик» и <span class="placeholder"><?= $contract->user->name; ?></span>, именуемый в дальнейшем «Учащийся», с другой стороны, заключили настоящий Договор о нижеследующем:</p>

    <?= $this->render('_body', ['contract' => $contract]); ?>

    <table>
        <tr>
            <?= $this->render('_company_contact', ['contract' => $contract]); ?>
            <td style="width: 50%; text-align: left; vertical-align: top;">
                <div style="text-decoration: underline;"><b>Заказчик:</b></div><br>

                ФИО Заказчика: <span style="text-decoration: underline; padding: 0 2mm;"><?= $contract->user->name; ?></span><br>

                <span style="display: inline-block; width: 1.5cm">Адрес: </span><span class="placeholder" style="width: 7.5cm"></span><br>
                <span class="placeholder" style="width: 9cm"></span><br>

                Паспорт: серия <span class="placeholder" style="width: 1cm"></span> № <span class="placeholder" style="width: 2cm"></span><br>
                выдан «<span class="placeholder" style="width: 6mm"></span>»
                «<span class="placeholder" style="width: 6mm"></span>»
                «<span class="placeholder" style="width: 12mm"></span>» г.<br>

                <span style="display: inline-block; width: 2.5cm">Кем выдан: </span><span class="placeholder" style="width: 6.5cm"></span><br>
                <span class="placeholder" style="width: 9cm"></span><br>

                Тел.: <span class="placeholder" style="width: 3cm;"><?= $contract->user->parent ? $contract->user->parent->phoneFull : $contract->user->phoneFull; ?></span>
                <span class="placeholder" style="width: 3cm;"><?= $contract->user->parent ? $contract->user->parent->phone2Full : $contract->user->phone2Full; ?></span><br>
                Тел. учащегося: <span class="placeholder" style="width: 3cm;"><?= $contract->user->phoneFull; ?></span><br><br><br>

                <div class="text-right"><span class="placeholder" style="width: 4cm;"></span></div>
                <div class="text-right"><span class="text-center" style="display: inline-block; width: 5cm;">(подпись)</span></div>
            </td>
        </tr>
    </table>
</div>