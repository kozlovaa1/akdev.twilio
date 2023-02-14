<?php if(!$USER->IsAdmin()) return;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;

Loc::loadMessages(__FILE__);

Loader::includeModule($mid);

//
// config
//

$options = [
	[
        'tab' => 'Настройки',
        'options' => [
            [
                'group' => 'Twilio API',
            ],
            'name1' => 'TWILIO_ACCOUNT_SID',
            [
                'name' => 'TWILIO_ACCOUNT_SID',
                'label' => 'TWILIO_ACCOUNT_SID',
                'type' => 'text',
            ],
            'name2' => 'TWILIO_AUTH_TOKEN',
            [
                'name' => 'TWILIO_AUTH_TOKEN',
                'label' => 'TWILIO_AUTH_TOKEN',
                'type' => 'text',
            ],
            [
                'group' => 'Номера телефонов',
            ],
            'name3' => 'Номер телефона отправителя',
            [
				'name' => 'TWILIO_SENDER',
                'label' => 'TWILIO_SENDER',
                'type' => 'text',
			],
            'name4' => 'Режим тестирования (все сообщения отправляются тестовому получателю)',
            [
				'name' => 'TWILIO_TEST_MODE',
                'label' => 'TWILIO_TEST_MODE',
                'type' => 'checkbox',
			],
            'name5' => 'Номер телефона тестового получателя',
            [
				'name' => 'TWILIO_RECIPIENT',
                'label' => 'TWILIO_RECIPIENT',
                'type' => 'text',
			],
        ],
    ],
];

//
// process
//

$optionJson = [];
$optionNames = [];
foreach ($options as $optionTab) {
    foreach ($optionTab['options'] as $name => $value) {
        if (is_string($value)) {
            $optionNames[] = $name;
        }
        else if (is_array($value)) {
            $name = $value['name'] ?? null;
			if ($name) {
				$optionNames[] = $name;

				$multiple = (bool) ($value['multiple'] ?? false);
				if ($multiple) {
					$optionJson[] = $name;
				}
			}
        }
    }
}

$isSave = $_POST['save'] ?? $_POST['apply'] ?? false;
if ($isSave && check_bitrix_sessid()) {
	foreach ($optionNames as $name) {
		$value = $_POST[$name] ?? null;
		if (is_array($value)) {
			$value = array_filter($value);
		}
		if (in_array($name, $optionJson)) {
			$value = json_encode($value);
		}
		Option::set($mid, $name, "{$value}");
	}
}


$aTabs = array_map(function($item) {
    static $i = 0;
    return [
        'ICON' => '',
        'DIV' => 'tab'.($i++),
        'TAB' => $item['tab'],
        'TITLE' => $item['tab'],
    ];
}, $options);

$tabControl = new CAdminTabControl('tabControl', $aTabs);
$actionUrl = $APPLICATION->GetCurPage() ."?mid=".urlencode($mid)."&lang=".LANGUAGE_ID;

?>
<form method="post" action="<?= $actionUrl ?>">
    <?php
	echo bitrix_sessid_post();

	$tabControl->Begin();
	foreach ($options as $optionTab) {
        $tabControl->BeginNextTab();
        foreach ($optionTab['options'] as $name => $value) {
            if (is_string($value)) {
                $optionName = $name;
                $optionLabel = $value;
                $optionType = "text";
            }
            else if (is_array($value)) {
                $optionGroup = $value['group'] ?? null;
                if ($optionGroup) {
                    echo "<tr class='heading'><td colspan='2'>{$optionGroup}</td></tr>";
                    continue;
                }

				$optionType = $value['type'] ?? 'text';
				if ($optionType === 'file') {
					$file = $value['file'];
					if ($file) {
						echo "<tr><td colspan='2'>";
						@include $file;
						echo "</td></tr>";
					}
					continue;
				}

                $optionName = $value['name'] ?? null;
                if (!$optionName) {
                    continue;
                }

                $optionLabel = $value['label'] ?? $optionName;
            }

            $optionValue = (string) Option::get($mid, $optionName);
            ?>
            <tr>
                <td class="adm-detail-content-cell-l">
                    <?= $optionLabel ?>
                </td>
                <td class="adm-detail-content-cell-r">
                    <?php
                    switch ($optionType) {
                        case 'select':
							$selectValues = $value['values'];
							$isAssocSelectValues = !empty(array_diff_assoc(
								array_keys($selectValues),
								range(0, count($selectValues)-1)
							));

							$multiple = (bool) ($value['multiple'] ?? false);
							$size = 1;
							if ($multiple) {
								$size = 5;
								$optionName .= "[]";
							}
							
							echo "<select class='typeselect' name='{$optionName}' size='{$size}' ".($multiple ? 'multiple' : '').">";
							foreach ($selectValues as $key => $item) {
								if ($isAssocSelectValues) {
									$selectOptionValue = $key;
								}
								else {
									$selectOptionValue = $item;
								}

								if ($multiple) {
									$selected = in_array($selectOptionValue, $optionValue) ? "selected" : "";
								}
								else {
									$selected = "{$selectOptionValue}" === "{$optionValue}" ? "selected" : "";
								}

								echo "<option value='{$selectOptionValue}' {$selected}>{$item}</option>";
							}
							echo "</select>";
							break;

                        case 'checkbox':
                            $optionValue = $optionValue ?: 'N';
                            $checked = $optionValue == 'Y' ? "checked" : "";
                            echo "
                            <input class='adm-designed-checkbox' type='checkbox' id='{$optionName}' name='{$optionName}' value='Y' {$checked}>
                            <label class='adm-designed-checkbox-label' for='{$optionName}'></label>
                            ";
                            break;

                        default:
							$multiple = (bool) ($value['multiple'] ?? false);
							if ($multiple) {
								$optionName .= "[]";
								foreach ($optionValue as $item) {
									if (empty($item)) {
										continue;
									}
									echo "<div><input type='{$optionType}' name='{$optionName}' value='{$item}'></div>";
								}
								echo "<div>
								<input type='button' value='Добавить' onclick='addTemplateRow(this);'>
								<div class='jsTemplateRow' style='display:none;'>
									<input type='{$optionType}' name='{$optionName}' value=''>
								</div>
								</div>";
							}
							else {
								echo "<input type='{$optionType}' name='{$optionName}' value='{$optionValue}'>";
							}
							break;
                    }
                    ?>
                </td>
            </tr>
            <?php
        }
	}

	$tabControl->Buttons([]);
	$tabControl->End();

?>
</form>
<style media="screen">
	.adm-detail-content-cell-l {
		width: 50%;
	}
	.adm-detail-content-cell-r select {
		width: auto;
		max-width: 100%;
	}
	.adm-detail-content-cell-l,
	.adm-detail-content-cell-r {
		vertical-align: top;
	}
</style>
<script type="text/javascript">
	function addTemplateRow(btn) {
		var templateRow = btn.parentNode.querySelector('.jsTemplateRow')
		if (!templateRow) {
			return;
		}

		var targetElement = btn.parentNode.parentNode;
		if (!targetElement) {
			return;
		}

		var div = document.createElement('div')
		div.innerHTML = templateRow.innerHTML
		targetElement.insertBefore(
			div, targetElement.lastElementChild
		)
	}
</script>
