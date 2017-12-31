<?php
if(!function_exists("getCurrencySelectorHtml")) {
    function getCurrencySelectorHtml() {
        $multipleCurrencyList = json_decode(get_theme_mod("currency_list", ""));
        if($multipleCurrencyList != null) {
            $currentCurrency = "";
            if(isset($_COOKIE["stm_current_currency"])){
                $mc = explode("-", $_COOKIE["stm_current_currency"]);
                $currentCurrency = $mc[0];
            }

            $currency[0] = get_theme_mod("price_currency_name", "USD");
            if($multipleCurrencyList->currency != null) $currency = array_merge($currency, explode(",", $multipleCurrencyList->currency));

            $symbol[0] = get_theme_mod("price_currency", "$");
            if($multipleCurrencyList->symbol != null) $symbol = array_merge($symbol, explode(",", $multipleCurrencyList->symbol));

            $to[0] = "1";
            $to = array_merge($to, explode(",", $multipleCurrencyList->to));

            $selectHtml = "<div class='stm-multiple-currency-wrap'><select data-translate='" . esc_html__("Currency (%s)", "motors") . "' data-class='stm-multi-currency' name='stm-multi-currency'>";
            for($q=0;$q<count($currency);$q++) {
                $selected = ($symbol[$q] == $currentCurrency) ? "selected" : "";
                $val = html_entity_decode($symbol[$q]) . "-" . $to[$q];
                $currencyTitle = $currency[$q];

                if(!isset($_COOKIE["stm_current_currency"]) && $q == 0 || !empty($selected)) {
                    $currencyTitle = sprintf(esc_html__("Currency (%s)", "motors"), $currency[$q]);
                }

                $selectHtml .= "<option value='{$val}' " . $selected . ">{$currencyTitle}</option>";
            }
            $selectHtml .= "</select></div>";

            if(count($currency) > 1) {
                echo $selectHtml;
            }
        }
    }
}

if(!function_exists("getPriceHtml")) {
    function getConverPrice($price, $echo = false)
    {
		if(isset($_COOKIE["stm_current_currency"])) {
        	$cookie = explode("-", $_COOKIE["stm_current_currency"]);
        	$price = ($price * $cookie[1]);
        }

        return $price;
    }
}