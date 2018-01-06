<div class="content_padding typography">
<form method="POST" action="en/cart/success">
    <table class="">
    <tr>
        <td>Credit Card Type: <span class="mand">*</span></td>
        <td><select name="credit_card_type">
                <option value="Visa">Visa</option>
                <option value="MasterCard">MasterCard</option>
                <option value="Discover">Discover</option>
                <option value="Amex">American Express</option>
        </select>
        </td>
    </tr>

    <tr>
        <td>Credit Card Number: <span class="mand">*</span></td>
        <td><input type="text" name="credit_card_number" class="text" ></td>
    </tr>

    <tr>
        <td>Security Code(CVV2): <span class="mand">*</span></td>
        <td><input type="text" name="CVV2" maxlength="4" /></td>
    </tr>

    <tr>
        <td>Exipration Date: <span>*</span></td>
        <td>
            <select name="exp_month">
                <option value="1">01</option>
                <option value="2">02</option>
                <option value="3">03</option>
                <option value="4">04</option>
                <option value="5">05</option>
                <option value="6">06</option>
                <option value="7">07</option>
                <option value="8">08</option>
                <option value="9">09</option>
                <option value="10">10</option>
                <option value="11">11</option>
                <option value="12">12</option>
            </select>
            <select name="exp_year">
                <?
                    $from_year = date('Y');
                    $to_year = date('Y') + 5;
                    for($i = $from_year; $i <= $to_year; $i++){?>
                    <option value="<?=$i?>"><?=$i?></option>
                    <?}?>
            </select>
        </td>
    </tr>
    <tr>
        <td colspan="2"><input type="submit" value="Proceed" class="action"></td>
    </tr>
    </table>
</form>
</div>
