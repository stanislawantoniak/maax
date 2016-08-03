<?php
$_store = Mage::app()->getStore()->getCode();
if ($_store == Mage_Core_Model_Store::ADMIN_CODE) {
    $_store = Mage::getSingleton('adminhtml/config_data')->getStore();
}
$_helper = Mage::helper('snowfreshmail');
$_containerMaxWidth = $_helper->getPopupProperty('formcontainer_width', $_store) . $_helper->getPopupProperty('formcontainer_width_calculation', $_store);
?>
/** Generated at <?= date('d-m-Y H:i:s') ?> */

html {
    height: auto !important;
}

body {
    height: 100% !important;
    min-height: 100% !important;
    margin: 0 !important;
    paadding: 0 !important;
}

.freshmail-main-wrapper {
    display: table;
    height: 100%;
    left: 0;
    margin: auto;
    position: fixed;
    right: 0;
    top: 0;
    width: 100%;
    z-index: 9999;
}

.freshmail-main-wrapper__cell {
    display: table-cell;
    vertical-align: middle;
}

body:not(.adminhtml-system-config-edit) #freshmail_formcontainer {
    position: relative;
    margin: auto;
    z-index: 9999;
}

body:not(.adminhtml-system-config-edit) #freshmail_backdrop {
    z-index: 9998;
    opacity: 0.8;
    position: fixed;
    top: 0;
    left: 0;
    bottom: 0;
    right: 0;
    background: #656565;
}

.freshmail-container__close {
    position: absolute;
    top: -32px;
    right: -32px;
    width: 29px;
    height: 29px;
    display: block;
    border-radius: 30px;
    text-align: center;
    cursor: pointer;
    background-color: #<?= $_helper->getPopupProperty('formcontainer_background_color', $_store) ?>;
}

.freshmail-container__close svg {
    display: block;
    width: 29px;
    height: 29px;
    padding: 8px;
}

.freshmail-container__form--hide {
    display: none;
}

.freshmail-container {
    position: relative;
    width: 100%;
    max-width: <?= $_containerMaxWidth ?>;
    border: <?= $_helper->getPopupProperty('formcontainer_border_width', $_store); ?>px solid #<?= $_helper->getPopupProperty('formcontainer_border_color', $_store) ?>;
    padding: <?= $_helper->getPopupProperty('formcontainer_vertical_padding', $_store); ?>px <?= $_helper->getPopupProperty('formcontainer_horizontal_padding', $_store) ?>px;
    border-radius: <?= $_helper->getPopupProperty('formcontainer_border_radius', $_store); ?>px;
    background-color: #<?= $_helper->getPopupProperty('formcontainer_background_color', $_store) ?>;
    box-sizing: border-box;
}

.freshmail-container * {
    box-sizing: border-box;
}

.freshmail-container__message--success {
    padding: 5px 10px 5px 37px;
    border: 1px solid #86B469;
    color: #<?= $_helper->getPopupProperty('success_color', $_store) ?>;
    background-image: url(data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNTEyIiBoZWlnaHQ9IjUxMiIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KCiA8Zz4KICA8dGl0bGU+YmFja2dyb3VuZDwvdGl0bGU+CiAgPHJlY3QgZmlsbD0ibm9uZSIgaWQ9ImNhbnZhc19iYWNrZ3JvdW5kIiBoZWlnaHQ9IjQwMiIgd2lkdGg9IjU4MiIgeT0iLTEiIHg9Ii0xIi8+CiA8L2c+CiA8Zz4KICA8dGl0bGU+TGF5ZXIgMTwvdGl0bGU+CiAgPHBhdGggaWQ9InN2Z18xIiBmaWxsPSIjMTkxOTE5IiBkPSJtMjU2LDYuOTk4Yy0xMzcuNTMyOTk3LDAgLTI0OSwxMTEuNDY2OTk2IC0yNDksMjQ5LjAwMDAwMWMwLDEzNy41MzQwMTIgMTExLjQ2NzAwMywyNDguOTk5OTg1IDI0OSwyNDguOTk5OTg1czI0OSwtMTExLjQ2Njk4IDI0OSwtMjQ4Ljk5OTk4NWMwLC0xMzcuNTM0MDA0IC0xMTEuNDY3MDEsLTI0OS4wMDAwMDEgLTI0OSwtMjQ5LjAwMDAwMXptMCw0NzguMDgwMDAzYy0xMjYuMzA5MDA2LDAgLTIyOS4wOCwtMTAyLjc3MDk5NiAtMjI5LjA4LC0yMjkuMDgwOTk0YzAsLTEyNi4zMDk5OTggMTAyLjc3MDk5NCwtMjI5LjA4IDIyOS4wOCwtMjI5LjA4YzEyNi4zMDk5OTgsMCAyMjkuMDc5OTg3LDEwMi43NzA5OTQgMjI5LjA3OTk4NywyMjkuMDhjMCwxMjYuMzA5OTk4IC0xMDIuNzY5OTg5LDIyOS4wODA5OTQgLTIyOS4wNzk5ODcsMjI5LjA4MDk5NHoiLz4KICA8cG9seWdvbiBpZD0ic3ZnXzIiIHBvaW50cz0iMzg0LjIzNSwxNTguMTkyIDIxNi45MTksMzI1LjUxOCAxMjcuODYyLDIzNi40ODEgMTEzLjcyLDI1MC42MjQgMjE2LjkxOSwzNTMuODAzIDM5OC4yOCwxNzIuMzM0ICAgIiBmaWxsPSIjMDBiZjVmIi8+CiA8L2c+Cjwvc3ZnPg==);
    background-repeat: no-repeat;
    background-position: 10px center;
    background-size: 20px 20px;
}

.freshmail-container__success-icon--show {
    display: block;
}

.freshmail-container__message--error {
    margin-bottom: 10px;
    padding: 5px 10px;
    border: 1px solid #df280a;
    color: #<?= $_helper->getPopupProperty('error_color', $_store) ?>;
}

.freshmail-container__header {
    display: <?= !$_helper->getPopupProperty('textheader_display', $_store) ? 'none' : 'block' ?>;
    margin-bottom: 15px;
    color: #<?= $_helper->getPopupProperty('textheader_color', $_store) ?>;
    font-size: <?= $_helper->getPopupProperty('textheader_size', $_store) ?>px;
    line-height: <?= $_helper->getPopupProperty('textheader_size', $_store) ?>px;
    text-align: <?= $_helper->getPopupProperty('textheader_alignment', $_store) ?>;
    text-transform: none;
}

.freshmail-container__subheader {
    display: <?= !$_helper->getPopupProperty('subheader_display', $_store) ? 'none' : 'block' ?>;
    margin-bottom: 15px;
    color: #<?= $_helper->getPopupProperty('subheader_color', $_store) ?>;
    font-size: <?= $_helper->getPopupProperty('subheader_size', $_store) ?>px;
    line-height: <?= $_helper->getPopupProperty('subheader_size', $_store) ?>px;
    text-align: <?= $_helper->getPopupProperty('subheader_alignment', $_store) ?>;
    text-transform: none;
}

.freshmail-container__subheader p {
    margin: 0;
}

.freshmail-container__image {
    max-width: 100%;
    margin-bottom: 15px;
    text-align: <?= $_helper->getPopupProperty('form_photo_alignment', $_store) ?>;
}

.freshmail-container__image img {
    display: inline-block;
    max-width: 100%;
<?php
if ($_helper->getPopupProperty('form_photo_size', $_store) == 'scale_with_form') {
    echo 'width: ' . $_helper->getPopupProperty('field_width', $_store) . $_helper->getPopupProperty('field_width_calculation', $_store) . ';';
}
?>
    vertical-align: bottom;
}

.freshmail-container__field-row {
    font-size: 0;
}

.freshmail-container__field-row + .freshmail-container__field-row {
    margin-top: 10px;
}

.freshmail-container__label {
    display: <?= $_helper->getPopupProperty('label_position', $_store) ?>;
    width: <?= $_helper->getPopupProperty('label_width', $_store) . $_helper->getPopupProperty('label_width_calculation', $_store) ?>;
    padding: 0 7px;
    line-height: <?= $_helper->getPopupProperty('field_height', $_store) . $_helper->getPopupProperty('field_height_calculation', $_store) ?>;
    vertical-align: top;
    color: #<?= $_helper->getPopupProperty('label_color', $_store) ?>;
    font-size: <?php echo $_helper->getPopupProperty('label_size', $_store) ?>px;
    text-align: right;
    text-align: <?= $_helper->getPopupProperty('label_text_alignment', $_store) ?>;
<?php
switch ($_helper->getPopupProperty('label_style', $_store)) {
    case 'bold':
        echo 'font-weight: bold;';
        break;
    case 'italic':
        echo 'font-style: italic;';
        break;
    case 'bold italic':
        echo 'font-weight: bold;';
        echo 'font-style: italic;';
        break;
}
if ($_helper->getPopupProperty('label_position', $_store) == 'block') {
    echo 'margin: auto;';
    echo 'text-align: center;';
}
?>
}

.freshmail-container__field-container {
    display: inline-block;;
    width: <?= $_helper->getPopupProperty('field_width', $_store) . $_helper->getPopupProperty('field_width_calculation', $_store) ?>;
    height: <?= $_helper->getPopupProperty('field_height', $_store) . $_helper->getPopupProperty('field_height_calculation', $_store) ?>;
}

.freshmail-container .freshmail-container__field {
    border: <?= $_helper->getPopupProperty('field_border_width', $_store) .'px solid #' . $_helper->getPopupProperty('field_border_color', $_store) ?>;
    height: <?= $_helper->getPopupProperty('field_height', $_store) . $_helper->getPopupProperty('field_height_calculation', $_store) ?>;
    color: #<?= $_helper->getPopupProperty('field_color', $_store); ?>;
    width: 100%;
    padding: 0 8px;
    font-size: <?= $_helper->getPopupProperty('field_size', $_store) ?>px;
<?php
if ($_helper->getPopupProperty('label_position', $_store) == 'block') {
    echo 'display: block;';
    echo 'margin: auto;';
}
?>
}

.freshmail-container__field:focus {
    outline: 0;
}

.validation-advice {
    margin-top: 2px;
    font-size: 12px;
}

.freshmail-container__agreement {
    display: <?= !$_helper->getPopupProperty('agreement_display', $_store) ? 'none' : 'block' ?>;
    margin-top: 15px;
    text-align: <?= $_helper->getPopupProperty('agreement_alignment', $_store) ?>;
}

.freshmail-container__agreement .validation-advice {
    display: none;
}

.freshmail-container__agreement-label {
    color: #<?= $_helper->getPopupProperty('agreement_color', $_store) ?>;
    font-size: <?= $_helper->getPopupProperty('agreement_size', $_store) ?>px;
}

.freshmail-container__button_wrapper {
    text-align: <?= $_helper->getPopupProperty('button_alignment', $_store) ?>;
}

.freshmail-container__button {
    width: <?= $_helper->getPopupProperty('button_width', $_store); ?><?= $_helper->getPopupProperty('button_width_calculation', $_store) ?>;
    height: <?= $_helper->getPopupProperty('button_height', $_store); ?>px;
    margin-top: 15px;
    border: <?= $_helper->getPopupProperty('button_border_width', $_store); ?>px solid #<?= $_helper->getPopupProperty('button_border_color', $_store) ?>;
    color: #<?= $_helper->getPopupProperty('button_color', $_store) ?>;
    font-size: <?= $_helper->getPopupProperty('button_size', $_store) ?>px;
    cursor: pointer;
    background: #<?= $_helper->getPopupProperty('button_background_color', $_store) ?>;
}

.freshmail-container__button--disabled {
    background-image: url(data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz48c3ZnIHdpZHRoPSczMnB4JyBoZWlnaHQ9JzMycHgnIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgdmlld0JveD0iMCAwIDEwMCAxMDAiIHByZXNlcnZlQXNwZWN0UmF0aW89InhNaWRZTWlkIiBjbGFzcz0idWlsLXJpbmctYWx0Ij48cmVjdCB4PSIwIiB5PSIwIiB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgZmlsbD0ibm9uZSIgY2xhc3M9ImJrIj48L3JlY3Q+PGNpcmNsZSBjeD0iNTAiIGN5PSI1MCIgcj0iNDAiIHN0cm9rZT0iI2NjYyIgZmlsbD0ibm9uZSIgc3Ryb2tlLXdpZHRoPSIxMCIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIj48L2NpcmNsZT48Y2lyY2xlIGN4PSI1MCIgY3k9IjUwIiByPSI0MCIgc3Ryb2tlPSIjZjFmMWYxIiBmaWxsPSJub25lIiBzdHJva2Utd2lkdGg9IjYiIHN0cm9rZS1saW5lY2FwPSJyb3VuZCI+PGFuaW1hdGUgYXR0cmlidXRlTmFtZT0ic3Ryb2tlLWRhc2hvZmZzZXQiIGR1cj0iMnMiIHJlcGVhdENvdW50PSJpbmRlZmluaXRlIiBmcm9tPSIwIiB0bz0iNTAyIj48L2FuaW1hdGU+PGFuaW1hdGUgYXR0cmlidXRlTmFtZT0ic3Ryb2tlLWRhc2hhcnJheSIgZHVyPSIycyIgcmVwZWF0Q291bnQ9ImluZGVmaW5pdGUiIHZhbHVlcz0iMTUwLjYgMTAwLjQ7MSAyNTA7MTUwLjYgMTAwLjQiPjwvYW5pbWF0ZT48L2NpcmNsZT48L3N2Zz4=);
    background-repeat: no-repeat;
    background-position: 95% center;
    background-size: 20px;
}

.freshmail-container__button:hover {
    background: #<?= $_helper->getPopupProperty('button_hovered_background_color', $_store) ?>;
    border: <?= $_helper->getPopupProperty('button_border_width', $_store); ?>px solid #<?= $_helper->getPopupProperty('button_hovered_border_color', $_store) ?>;
    color: #<?= $_helper->getPopupProperty('button_hovered_color', $_store) ?>;
}

@media screen and (max-width: <?= $_helper->getPopupProperty('formcontainer_width', $_store) + 60 . 'px' ?>) {
    body:not(.adminhtml-system-config-edit) #freshmail_formcontainer {
        top: 20px;
    }

    .freshmail-container__close {
        display: none;
    }

    .freshmail-container {
        width: 93%;
        min-width: 320px;
    }

    .freshmail-container__label {
        width: 100%;
        padding-left: 0;
        padding-bottom: 3px;
        text-align: left;
    }

    .freshmail-container__field-container {
        width: 100%;
    }

    .freshmail-container .freshmail-container__field[type="email"] {
        width: 100%;
    }

    .freshmail-container__agreement {
        position: relative;
        text-align: left;
    }

    .freshmail-container__agreement-label {
        padding-left: 20px;
    }

    input[type="checkbox"] {
        position: absolute;
        top: 3px;
        left: 0;
    }
}
