<table>
	<tr>
		<td width="15%" align="right">
			Name:
		</td>
		<td width="85%">
			<?=$aOutput['name'] ?>
		</td>
	</tr>
	<tr>
		<td align="right">
			Email:
		</td>
		<td>
			<?=$aOutput['email'] ?>
		</td>
	</tr>
	<tr>
		<td align="right">
			Address:
		</td>
		<td>
			<?=$aOutput['address'] ?>
		</td>
	</tr>
	<tr>
		<td align="right">
			City:
		</td>
		<td>
			<?=$aOutput['city'] ?>
		</td>
	</tr>
	<tr>
		<td align="right">
			State:
		</td>
		<td>
			<?=$aOutput['state'] ?>
		</td>
	</tr>
	<tr>
		<td align="right">
			Zip Code:
		</td>
		<td>
			<?=$aOutput['postal_code'] ?>
		</td>
	</tr>
	<tr>
		<td align="right">
			Phone:
		</td>
		<td>
			<?=$aOutput['phone'] ?>
		</td>
	</tr>
	<tr>
		<td align="right">
			Comments:
		</td>
		<td>
			<?=nl2br( $aOutput['msg'] ) ?>
		</td>
	</tr>
</table>