<!-- BEGIN IMAGES_LIST -->
<li class="file"><input type="checkbox" name="aFilesDelete[$aData[iFile]]" value="1" /><a href="$config[dir_files]$aData[sFileName]" target="_blank">$aData[sFileName]</a></li>
<li class="options">
  <select name="aFilesSizes1[$aData[iFile]]" title="$lang['Thumbnail_1']">
    $aData[sSizes1Select]
  </select>
  <select name="aFilesSizes2[$aData[iFile]]" title="$lang['Thumbnail_2']">
    $aData[sSizes2Select]
  </select>
  <input type="text" name="aFilesDescription[$aData[iFile]]" value="$aData[sDescription]" size="25" class="input" title="$lang['Description']" />
  <input type="text" name="aFilesPositions[$aData[iFile]]" value="$aData[iPosition]" size="2" maxlength="3" class="inputr" title="$lang['Position']" />
  <select name="aFilesTypes[$aData[iFile]]" title="$lang['Photo_place']" >
    $aData[sPhotoTypesSelect]
  </select>
</li>
<!-- END IMAGES_LIST -->
<!-- BEGIN FILES_LIST -->
<li class="file"><input type="checkbox" name="aFilesDelete[$aData[iFile]]" value="1" /><a href="$config[dir_files]$aData[sFileName]" target="_blank">$aData[sFileName]</a></li>
<li class="options">
  <input type="text" name="aFilesDescription[$aData[iFile]]" value="$aData[sDescription]" size="25" class="input" title="$lang['Description']" />
  <input type="text" name="aFilesPositions[$aData[iFile]]" value="$aData[iPosition]" size="2" maxlength="3" class="inputr" title="$lang['Position']" />
</li>
<!-- END FILES_LIST -->
<!-- BEGIN FILES_HEAD -->
<h3>$lang['Files']</h3>
<div id="filesList" style="height:$aData[iHeight]px;">
  <ul>
    <li class="file">
      $lang['Delete']
    </li>
<!-- END FILES_HEAD -->
<!-- BEGIN FILES_FOOT -->
</ul>
</div>
<!-- END FILES_FOOT -->

<!-- BEGIN IMAGES_LIST_DIR --><li class="file"><input type="checkbox" name="aDirFiles[$aData[iFile]]" value="$aData[sFileName]" onclick="displayBlock( 'fileDirDetails$aData[iFile]' );" /><a href="$config[dir_files]$aData[sFileName]" target="_blank" class="a$aData['iStatus']">$aData[sFileName]</a></li><li class="options" id="fileDirDetails$aData[iFile]">
  <select name="aDirFilesSizes1[$aData[iFile]]" title="$lang['Thumbnail_1']">$sSize1Select</select>
  <select name="aDirFilesSizes2[$aData[iFile]]" title="$lang['Thumbnail_2']">$sSize2Select</select>
  <input type="text" name="aDirFilesDescriptions[$aData[iFile]]" size="25" class="input" title="$lang['Description']" />
  <input type="text" name="aDirFilesPositions[$aData[iFile]]" value="0" size="2" maxlength="3" class="inputr" title="$lang['Position']"/>
  <select name="aDirFilesTypes[$aData[iFile]]" title="$lang['Photo_place']">$sPhotoTypesSelect</select>
</li><!-- END IMAGES_LIST_DIR -->
<!-- BEGIN FILES_LIST_DIR --><li class="file"><input type="checkbox" name="aDirFiles[$aData[iFile]]" value="$aData[sFileName]" onclick="displayBlock( 'fileDirDetails$aData[iFile]' );" /><a href="$config[dir_files]$aData[sFileName]" target="_blank" class="a$aData['iStatus']">$aData[sFileName]</a></li><li class="options" id="fileDirDetails$aData[iFile]">
  <input type="text" name="aDirFilesDescriptions[$aData[iFile]]" size="25" class="input" title="$lang['Description']" />
  <input type="text" name="aDirFilesPositions[$aData[iFile]]" value="0" size="2" maxlength="3" class="inputr" title="$lang['Position']" />
</li><!-- END FILES_LIST_DIR -->
<!-- BEGIN FILES_HEAD_DIR -->
<h3>$lang['Files_on_server']</h3>
<div id="filesDir"><ul>
<li class="file">$lang['Select']</li>
<!-- END FILES_HEAD_DIR -->
<!-- BEGIN FILES_FOOT_DIR --></ul></div><!-- END FILES_FOOT_DIR -->

<!-- BEGIN FILES_FORM -->
<h3>$lang['Files_from_computer']</h3>
<div id="filesForm" class="$sDisplayAllFiles">
  <ul>
    <li class="file">
      $lang['File']&nbsp;&nbsp;<input type="file" name="aNewFiles[]" value="" size="30" class="input" onclick="displayBlock( 'fileFormDetails1', this );" />
    </li>
    <li class="options" id="fileFormDetails1">
      <table cellspacing="0">
        <tr>
          <th>
            $lang['Description']
          </th>
          <td>
            <input type="text" name="aNewFilesDescriptions[]" value="" size="40" class="input" />
          </td>
        </tr>
        <tr>
          <th>
            $lang['Position']
          </th>
          <td>
            <input type="text" name="aNewFilesPositions[]" value="0" maxlength="3" size="2" class="inputr" />
          </td>
        </tr>
        <tr>
          <th>
            $lang['Thumbnail_1']
          </th>
          <td>
            <select name="aNewFilesSizes1[]">$sSize1Select</select>
          </td>
        </tr>
        <tr>
          <th>
            $lang['Thumbnail_2']
          </th>
          <td>
            <select name="aNewFilesSizes2[]">$sSize2Select</select>
          </td>
        </tr>
        <tr>
          <th>
            $lang['Photo_place']
          </th>
          <td>
            <select name="aNewFilesTypes[]">$sPhotoTypesSelect</select>
          </td>
        </tr>
      </table>
    </li>
    <li class="file">
      $lang['File']&nbsp;&nbsp;<input type="file" name="aNewFiles[]" value="" size="30" class="input" onclick="displayBlock( 'fileFormDetails2', this );" />
    </li>
    <li class="options" id="fileFormDetails2">
      <table cellspacing="0">
        <tr>
          <th>
            $lang['Description']
          </th>
          <td>
            <input type="text" name="aNewFilesDescriptions[]" value="" size="40" class="input" />
          </td>
        </tr>
        <tr>
          <th>
            $lang['Position']
          </th>
          <td>
            <input type="text" name="aNewFilesPositions[]" value="0" maxlength="3" size="2" class="inputr" />
          </td>
        </tr>
        <tr>
          <th>
            $lang['Thumbnail_1']
          </th>
          <td>
            <select name="aNewFilesSizes1[]">$sSize1Select</select>
          </td>
        </tr>
        <tr>
          <th>
            $lang['Thumbnail_2']
          </th>
          <td>
            <select name="aNewFilesSizes2[]">$sSize2Select</select>
          </td>
        </tr>
        <tr>
          <th>
            $lang['Photo_place']
          </th>
          <td>
            <select name="aNewFilesTypes[]">$sPhotoTypesSelect</select>
          </td>
        </tr>
      </table>
    </li>
    <li class="file">
      $lang['File']&nbsp;&nbsp;<input type="file" name="aNewFiles[]" value="" size="30" class="input" onclick="displayBlock( 'fileFormDetails3', this );" />
    </li>
    <li class="options" id="fileFormDetails3">
      <table cellspacing="0">
        <tr>
          <th>
            $lang['Description']
          </th>
          <td>
            <input type="text" name="aNewFilesDescriptions[]" value="" size="40" class="input" />
          </td>
        </tr>
        <tr>
          <th>
            $lang['Position']
          </th>
          <td>
            <input type="text" name="aNewFilesPositions[]" value="0" maxlength="3" size="2" class="inputr" />
          </td>
        </tr>
        <tr>
          <th>
           $lang['Thumbnail_1']
          </th>
          <td>
            <select name="aNewFilesSizes1[]">$sSize1Select</select>
          </td>
        </tr>
        <tr>
          <th>
            $lang['Thumbnail_2']
          </th>
          <td>
            <select name="aNewFilesSizes2[]">$sSize2Select</select>
          </td>
        </tr>
        <tr>
          <th>
            $lang['Photo_place']
          </th>
          <td>
            <select name="aNewFilesTypes[]">$sPhotoTypesSelect</select>
          </td>
        </tr>
      </table>
    </li>
  </ul>
</div>
<!-- END FILES_FORM -->

<!-- BEGIN ALL_IMAGES_LIST -->
<tr class="l$aData[iStyle]">
  <td class="name">
    <a href="$config[dir_files]$aData[sFileName]" target="_blank">$aData[sFileName]</a>
  </td><td>
    <input type="text" name="aFilesDescription[$aData[iFile]]" value="$aData[sDescription]" class="input" size="40" />
  </td><td>
    <input type="text" name="aFilesPositions[$aData[iFile]]" value="$aData[iPosition]" class="inputr" size="2" maxlength="3" title="$lang['Position']" />
  </td><td>
    <select name="aFilesTypes[$aData[iFile]]">$aData[sPhotoTypesSelect]</select>
  </td><td>
    $aData[sLink]
  </td><td>
    <input type="checkbox" name="aFilesDelete[$aData[iFile]]" value="1" />
  </td>
</tr>
<!-- END ALL_IMAGES_LIST -->
<!-- BEGIN ALL_FILES_LIST -->
<tr class="l$aData[iStyle]">
  <td class="name">
    <a href="$config[dir_files]$aData[sFileName]" target="_blank">$aData[sFileName]</a>
  </td><td>
    <input type="text" name="aFilesDescription[$aData[iFile]]" value="$aData[sDescription]" class="input" size="40" />
  </td><td>
    <input type="text" name="aFilesPositions[$aData[iFile]]" value="$aData[iPosition]" class="inputr" size="2" maxlength="3" title="$lang['Position']" />
  </td><td>
    &nbsp;
  </td><td>
    $aData[sLink]
  </td><td>
    <input type="checkbox" name="aFilesDelete[$aData[iFile]]" value="1" />
  </td>
</tr>
<!-- END ALL_FILES_LIST -->
<!-- BEGIN ALL_HEAD -->
<h1><img src="$config[dir_templates]admin/img/ico_files.gif" alt="$lang['Files']" />$lang['Files'] - $sLinkType</h1>
<form action="?p=$p&amp;iLinkType=$iLinkType" method="post">
  <fieldset>
  <table id="list" class="files" cellspacing="1">
    <thead>
      <tr class="save">
        <th colspan="6">
          <select name="iLinkType" onchange="redirectToUrl( '?p=$p&amp;iLinkType='+this.value )">
            $sLinkTypeSelect
          </select>
          <input type="submit" name="sOption" value="$lang['save'] &raquo;" />
        </th>
      </tr>
      <tr>
        <td class="name">$lang['Name']</td>
        <td>$lang['Description']</td>
        <td class="position">$lang['Position']</td>
        <td>$lang['Photo_place']</td>
        <td>$lang['Added_to']</td>
        <td class="status">$lang['Delete']</td>
      </tr>
    </thead>
    <tfoot>
      <tr class="save">
        <th colspan="6">
          <input type="submit" name="sOption" value="$lang['save'] &raquo;" />
        </th>
      </tr>
    </tfoot>
    <tbody>
<!-- END ALL_HEAD -->
<!-- BEGIN ALL_FOOT -->
</tbody></table></fieldset></form><!-- END ALL_FOOT -->