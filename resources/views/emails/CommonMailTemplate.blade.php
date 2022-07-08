<table width="600" cellpadding="2" style="font-family: arial; border: 1px solid #ddd; border-collapse: collapse;">
	<tr>
		<td align="left" valign="middle">
			<img src="{{ $image_url['blue_logo_img_url'] }}" alt="Gurdev Hospital" style="vertical-align: middle; width: 250px; margin: 0 auto; display: block;" />
		</td>
	</tr>
	<tr>
		<td align="center" style="padding: 40px 20px; line-height: 1.6;">
			<img src="{{ $image_url['smile_img_url'] }}" alt="Thank You" width="100" />
			<h2 style="text-transform: uppercase; color: #2C72EC;">Appointment Request</h2>
			<h4>Hello Team,</h4>
			<h6>Appointment has been requested by new user, Here are the basic details:</h6>
			<div>
				<div><b>Patient Name: </b> {{ $data['u_full_name'] }}</div>
				<div><b>Mobile number: </b> {{ $data['u_phone_number'] }}</div>
				<div><b>Address: </b> {{ $data['u_address'] }}</div>
				<div><b>Date of birth: </b> {{ $data['u_dob'] }}</div>
				<div><b>Email: </b> {{ $data['u_email'] }}</div>
			</div>
			<h4><b>Comment :<b> {{ $data['comment'] }} </h4>
			<h5 style="margin-bottom: 0; "><strong>Sent on:</strong> {{$data['appointment_date']}}</h5>
		</td>
	</tr>
</table>