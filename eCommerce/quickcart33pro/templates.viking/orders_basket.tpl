<!-- BEGIN BASKET_LIST -->
<tr class="l$aData[sStyle]">
  <th>
    <a href="$aData[sLinkName]">$aData[sName]</a>
  </th>
  <td class="price">
    &#36;&nbsp;$aData[sPrice]
  </td>
  <td class="quantity">
    <label for="quantity$aData[iProduct]">$lang[Quantity]</label><input type="text" name="aProducts[$aData[iProduct]]" value="$aData[iQuantity]" size="3" maxlength="4" class="input" id="quantity$aData[iProduct]" alt="int" />
  </td>
  <td class="summary">
    &#36;&nbsp;$aData[sSummary]
  </td>
  <td class="del">
    <a href="$aData[sLinkDelete]">$lang[Basket_delete]</a>
  </td>
</tr>
<!-- END BASKET_LIST -->
<!-- BEGIN BASKET_HEAD -->
<script type="text/javascript" src="$config[dir_core]checkForm.js"></script>
<div id="basket">
  <div class="info">$lang[Basket_info]</div>
  <form method="post" action="" onsubmit="return checkForm( this )">
    <fieldset id="orderedProducts">
      <table cellspacing="0">
        <thead>
          <tr>
            <td class="name">
              $lang[Name]
            </td>
            <td class="price">
              <em>$lang[Price]</em>
            </td>
            <td class="quantity">
              $lang[Quantity]
            </td>
            <td class="summary">
              <em>$lang[Summary]</em>
            </td>
            <td class="options">&nbsp;</td>
          </tr>
        </thead>
        <tfoot>
          <tr id="recount">
            <td colspan="2">&nbsp;</td>
            <td>
              <input type="submit" value="$lang[Basket_update]" class="submit" />
            </td>
            <td colspan="2">&nbsp;</td>
          </tr>
          <tr class="summaryProducts">
            <th colspan="3">
              $lang[Summary]
            </th>
            <td id="summary">
              &#36;&nbsp;$aData[sProductsSummary]
            </td>
            <td>&nbsp;</td>
          </tr>
          <tr class="buttons">
            <td id="save">
              <input type="submit" name="sSave" value="$lang[Save_basket]" class="submit" />
              <button onClick="history.back(1);return false;" class="submit">Back to Shopping</button>
            </td>
            <td colspan="4" class="nextStep">
              $aData[sZagielInfo]
              <input type="submit" name="sNext" value="$lang[Basket_next]&nbsp;&nbsp;>>" class="submit" />
            </td>
          </tr>
        </tfoot>
        <tbody>
<!-- END BASKET_HEAD -->
<!-- BEGIN BASKET_FOOT -->
        </tbody>
      </table>
    </fieldset>
  </form>
</div>
<!-- END BASKET_FOOT -->
<!-- BEGIN BASKET_EMPTY -->
<div class="message" id="error">
  <h3>$lang['Basket_empty']</h3>
</div>
<!-- END BASKET_EMPTY -->
<!-- BEGIN ZAGIEL_INFO -->
<a href="javascript:void(0);" id="zagielInfo" onclick="windowNew( 'http://www.zagiel.com.pl/kalkulator/index_smart.php?action=getklientdet_si_rata&shopNo=$config[zagiel_id]&goodsValue=$aData['fProductsSummary']', 600, 370, 'Zagiel' )">$lang[Count_zagiel_payment]</a>
<!-- END ZAGIEL_INFO -->
