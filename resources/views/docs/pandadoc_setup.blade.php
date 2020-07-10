@extends('layouts.appdocs')
@section('title', 'Detailed setup guide for FusedDocs')
@section('content')
    <h1 class="title">Detailed setup guide for FusedDocs</h1>
	<style>
	ol {
	    padding-left:30px;
	}
	
	span.time {
	    float:right;
	    
	}
	.tbl-keypoints td{
		font-weight: bold;
		padding: 10px;
	}
	
	.innergrey td:first-child{
		padding-left: 20px;
	}

	.innergrey td{
		background: rgb(245, 245, 245);
		font-weight: normal;
	}

	

	td:last-child{
		text-align: right;
	}
	</style>
	<div class="inner-content panel-body setup-page">
		<div class="row">
			<div class="col-md-12">
				<iframe width="100% " height="505" src="https://www.youtube.com/embed/MnBDn44hGkM?rel=0&amp;showinfo=0" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
				    <h3 class="heading">Key Points & Timestamps</h3>
					<table class="tbl-keypoints" width="100%">
						<tr>
							<td width="90%">1. Basic Overview</td>
							<td width="10%">0:00</td>
						</tr>
						<tr>
							<td width="90%">2. Preparing Your Document Inside PandaDoc</td>
							<td width="10%">2:13</td>
						</tr>
							<tr class="innergrey">
								<td width="90%">a. Creating &amp; Assigning Document Roles</td>
								<td width="10%">2:53</td>
							</tr>
							<tr class="innergrey">
								<td width="90%">b. When &amp; How To Use Tokens and Fields</td>
								<td width="10%">4:30</td>
							</tr>
							<tr class="innergrey">
								<td width="90%">c. How to Create Tokens: Giving Meaningful Names</td>
								<td width="10%">5:38</td>
							</tr>
							<tr class="innergrey">
								<td width="90%">d. Do not put any characters in your tokens or field names</td>
								<td width="10%">7:03</td>
							</tr>
							<tr class="innergrey">
								<td width="90%">e. Assign Roles and Owners to Fields</td>
								<td width="10%">7:45</td>
							</tr>
						<tr>
							<td width="90%">3. Validating & Configuring Your Document Inside FusedDocs</td>
							<td width="10%">9:11</td>
						</tr>
							<tr class="innergrey">
								<td width="90%">a. Configure Tags For Contacts</td>
								<td width="10%">9:38</td>
							</tr>
							<tr class="innergrey">
								<td width="90%">b. Scan List of Fields Pulled from PandaDoc</td>
								<td width="10%">11:01</td>
							</tr>
						<tr>
							<td width="90%">4. Setting Up Your HTTP Post and Mapping Your Data</td>
							<td width="10%">13:53</td>
						</tr>
							<tr class="innergrey">
								<td width="90%">a. Choose a Trigger &amp; Add it to a Sequence</td>
								<td width="10%">14:29</td>
							</tr>
							<tr class="innergrey">
								<td width="90%">b. Add a HTTP Post Element</td>
								<td width="10%">15:16</td>
							</tr>
							<tr class="innergrey">
								<td width="90%">c. Add Parameters for Status and PricingTable</td>
								<td width="10%">17:50</td>
							</tr>
							<tr class="innergrey">
								<td width="90%">d. Add the merge fields into the right hand column</td>
								<td width="10%">18:34</td>
							</tr>
						<tr>
							<td width="90%">5. Running A Test</td>
							<td width="10%">20:06</td>
						</tr>
							<tr class="innergrey">
								<td width="90%">a. Test Trigger Tag</td>
								<td width="10%">20:06</td>
							</tr>
							<tr class="innergrey">
								<td width="90%">b. Use an Alternative Email for Owner and Contact</td>
								<td width="10%">21:17</td>
							</tr>
							<tr class="innergrey">
								<td width="90%">c. Apply Trigger Tag / Fill out your form</td>
								<td width="10%">22:03</td>
							</tr>
					</table>
			</div>

			<div class="col-md-12 ">
			    <h3 class="heading">Setup Notes Summary</h3>
				<p>To get setup quickly and easily with FusedDocs, it is important to understand exactly what it is FusedDocs is and what its purpose is.</p>

				<p>In short, FusedDocs is a simple interface and platform that allows you to map your contacts information, to the fields you have in your PandaDoc Templates.</p>
					
				<p>This can be something as simple as matching the first and last name fields inside Infusionsoft, to a single "ClientName" token you have inside your PandaDoc template.</p>

				<p>But, it can also include mapping things like contact owner information, and pricing or quote data.</p>

				<p>You will note that the core part of this is mapping your Infusionsoft Contact fields to the fields and tokens inside your PandaDoc Template. We use this “map” to get the correct data off the contact and push it into a new document based off your template.</p>

				<p>Now you understand the core purpose of the setup process, let’s go through the four simple stages of setup:</p>

			    <ol>
					<li><span>Preparing Your Document Inside PandaDoc</span></li>
                    <li><span>Validating & Configuring Your Document Inside FusedDocs</span></li>
					<li><span>Setting Up Your HTTP Post and Mapping Your Data</span></li>
					<li><span>Running A Test</span></li>
				</ol>
				<p>So, let’s jump into the first stage,</p>
				
				<h4 class="titles">1. Preparing Your Document Inside PandaDoc.</h4>
				
				<p>The objective of this step is to simply create the document you want inside PandaDoc as a template.</p>
				<p>They have countless tutorials on this, but there are a few tips we have to ensure your document is super compatible with FusedDocs & Infusionsoft.</p>
					
				<ul class="green-dots">
					<li><span>Decide on your roles and create them before building your document</span></li>
						<p>There should be a role created in your document for every person that you want to send the document too - typically these would be “Client” and “SalesRep” or similar.</p>
						<p>Then, when you create signatures, or fields for data to be entered, these should be assigned to one of these roles based on who is responsible for filling it out.</p>
						<p>This leads well onto point two, which is:</p>
						<li><span>Use tokens for data you will enter before you send a proposal, like clients name, or email and use fields for data that will be entered during the signing process.</span></li>
						<p>Understanding the difference between tokens and fields can be a little complicated, but put simply you use tokens for data that will be entered before a document is to be sent. This can be pricing, the company or persons names you will be sending to.</p>
						<p>This is what a token will look like in your template:</p>
						<img src="{{ asset('assets/images/proposalfor.jpg') }} "/>
						<p>This is what a field will look like:</p>
						<img src="{{ asset('assets/images/signature.jpg') }}"/>
						<p>Fields should be used for things like Signatures, dates etc that the contact will enter while signing a document.</p>
						<p>Tokens can be created at anytime simply by putting the token in square brackets like this [theprice].</p>
						<p>Note: PandaDoc will automatically create a first name, last name, email, company and phone token for every role in the document.</p>
						<li><span>When setting up your document, create your own tokens, name them in meaningful ways and don’t use those on the tokens tab.</span></li>
						<p>In the tokens tab you will see some “pre-made” tokens provided by PandaDoc - as mentioned these are created based on the roles in your document</p>
						<p>You can make tokens anytime you want simply put putting text inside square brackets - but I encourage you to give them meaningful names as it makes it easier later to know what goes there.</p>
						<p>For example, rather than [description], have [IntroLetterToClient] so you know exactly what is meant for that token.</p>
						<p>Naming them in this way will make it easier for you later to identify what data to put into this field.</p>						
						<li><span>Do not put any characters in your tokens or field names, including spaces or full stops.</span></li>
						<p>In short, this means your tokens should like this:</p>
						<p>[ClientFirstName] and not like [Client.First Name]</p>
						<p>These may seem like subtle differences, but systems interpret characters in different ways, so it is best to exclude them entirely.</p>
						<p>Plus, PandaDoc puts spaces in pre-made tokens, so it allows you to better differentiate between your own tokens and system tokens.</p>
						<li><span>Remember to assign roles and owners to your fields.</span></li>

						<p>When you create a document, you should create a ROLE for each person type you want to sign it.</p>
						<p>A simple example would be have a “Client” role and a “Sales Person” role.</p>
						<p>When creating fields, be sure to assign them to one or the other so they are filled out by the correct person. Additionally, when naming these fields for the API, you use the “Name” option - and remember to not include spaces, or characters.</p>					
						<img src="{{ asset('assets/images/assign-roles.jpg') }}"/>					
						<p>The client will only ever see what is typed into the Title field.</p>
						<p>So, now you’ve made your document let’s move to step</p>

				</ul>
					<h4 class="titles">2. Validating & Configuring Your Document Inside FusedDocs</h4>
						
						<p>If you log into FusedDocs, on the left hand side you will see “Manage Documents” - and if you go to this page you will see a list of the templates that are currently inside your PandaDoc account.</p>
						<p>If you select one, it will expand and we are now able to start the configuration and validation process.</p>
				<ul class="green-dots">
						<li><span>Firstly, we need to configure tags here:</span></li>
						<p>This is where you tell us what tag you want to apply to your contacts as they are sent a document, have opened it and have signed the document that you have sent them. You also have the option to “create new tags”, where we create some tags in your Infusionsoft account for you.</p>
						<p>Once you are happy with the tags applied, click “Save Tags”.</p>
						<li><span>The last step is to scan your list of fields that we have pulled from PandaDoc for this document.</span></li>
						<p>If there are any issues with spaces, naming or characters, we highlight them here.</p>
						<p>If there are no errors, then your document is save, configured and ready to go.</p>
					</li>
				</ul>	
					<h4 class="titles">3. Setting Up Your HTTP Post and Mapping Your Data</h4>
						<p>At this point, I’m going to skip over some Infusionsoft basics and assume you have some basic knowledge of how campaigns work. If you don’t, there will be a seperate video paired with this one as to how triggers work and why you may choose one of another - plus, Infusionsoft have there own guides on this as well.</p>
				<ul class="green-dots">	
						<li><span>You can trigger the sending of your document whatever way you choose</span></li>
						<p>This would lead into a sequence.</p>
						<li><span>Inside this sequence, add a HTTP post element, and go inside that element.</span></li>
						<p>From here, you need to click the PLUS icon and add row for every row that is shown in FusedDocs, and copy across the value.</p>
						<p>In the end, it will end up looking like this:</p>
						
						<img src="{{ asset('assets/images/httppost.jpg') }}"/>
						
						<li><span>Click the “MERGE” button, and row by row, add the merge fields into the right hand column.</span></li>
						<p>If you haven’t guessed it already, what you are doing here is telling us which CONTACT and/or OWNER data to put into which PandaDoc field, which is on the left.</p>
						<p>The top 3 fields, contactId, FuseKey and TemplateID are mandatory fields that we need and you can copy those values direct from FusedDocs.</p>
						<p>It will end up looking like this:</p>
						
						<img src="{{ asset('assets/images/merged-fields.jpg') }}"/>
						
						<p>For all the other fields you can merge a single, or multiple fields into one field, or use static values for your company name, the sender or things like price if you choose to.</p>
						<p>This is where naming your PandaDoc fields with descriptive names comes in handy.</p>
						<li><span>Add TWO parameters for Status and PricingTable</span></li>
						<p>If you send status as 0, rather than sending the new merged document, we will just create it as a draft for you to send later. If you set it as 1, we will send this document immediately.</p>
						<p>For PricingTable, if you set this to 1 we will pull the Product Interest data from the contacts most recently modified Opportunity. If you are not using Opportunities and Product Interests, set this to 0.</p>
				</ul>	
					<p>Once you are done here, you need to publish the HTTP post, the sequence AND the campaign.</p>
					<p>You are now ready for the final step, which is step</p>

					<h4 class="titles">4. Running a test.</h4>
				<ul class="green-dots">
						<p>For testing, we will not be using the testing feature inside the HTTP post - it simple does not work for our purposes.</p>
						<li><span>To run this test I have made a test trigger tag, <span style="font-weight:normal">but you can use your form or other trigger.</span></span></li>
						<li><span>I’ve then gone to a contact and ensured all the fields I wanted to merge are filled out, and they have an owner set, since I am merging in owner fields.</span></li>
						<p>Please note, PandaDoc does not let you trigger, send or create a document that has the same SENDER and RECEIVER email address.</p>
						<p>So, if you are planning on running a test to yourself, ensure that you use an alternative email for your OWNER and CONTACT or it will fail.</p>
						<li><span>Apply your trigger tag, or fill out your form</span></li>
						<li><span>That’s it, you’re done and you should have a document in your inbox.</span></li>
				</ul>
					<p>This finalises our setup guide - should you have additional questions or want clarifications on how to setup for specific scenarios, then please check out our support hub or contact our support team.</p>

					
			</div>
		</div>
	</div>
	
@endsection
