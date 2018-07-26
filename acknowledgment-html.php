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