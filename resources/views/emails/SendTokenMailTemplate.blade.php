<table width="600" cellpadding="2" style="font-family: arial; border: 1px solid #ddd; border-collapse: collapse;">
	<tr>
		<td align="left" valign="middle">
			<img src="{{ $image_url['blue_logo_img_url'] }}" alt="Gurdev Hospital" style="vertical-align: middle; width: 250px; margin: 0 auto; display: block;" />
		</td>
	</tr>
	<tr>
		<td align="center" style="padding: 40px 20px; line-height: 1.6;">
			<h2 style="text-transform: uppercase; color: #2C72EC;">Password Reset Token</h2>
			<h4>Hello Admin,</h4>
			<div>
				<div>Please <a href="{{ $data['resetlink'] }}">Click here</a> to reset your Password.</div>
			</div>
			
			<h5 style="margin-bottom: 0; "><strong>Thanks & Regards</strong></h5>
		</td>
	</tr>
</table>