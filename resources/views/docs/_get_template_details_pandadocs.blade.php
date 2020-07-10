
<form id="template-form">
    <input type="hidden" name="temp_name" id="temp_name" value="{{ $temp_name }}"/>
    <input type="hidden" name="temp_id" value="{{ $templateID }}"/>

<div class="panel-body">


	<div class="row">
		<div class="col-lg-12">
			<h3>Status Tag Settings</h3>
			<p>FuseSync is capable of tagging a user inside InfusionSoft when they have been sent, viewed or signed to automate your follow ups.</p>
			<p>Match the tags you want to add at each stage , or click the "Make Tags For Me" button and we will create these tags in a category </p>
		</div>
	</div>


	<div class="row marbtm">
		<div class="col-lg-5">
			<h3>When The Documents is this status... </h3>
		</div>
		<div class="col-lg-4">
			<h3>Apply this tag</h3>
		</div>
		<div class="col-lg-3"><br/>
			<button class="btn btn-success" onclick="saveCategory($(this))">Create Tags For Me <i class="fa"></i></button>
		</div>
	</div>

	@if ( count($document_status) )
		@foreach($document_status as $status )
			<div class="form-group">
				<div class="row">
					<div class="col-md-4">
						{{ ucfirst($status) }}
					</div>
					<div class="col-md-offset-1 col-md-7">
						<select class="form-control {{ $status }}" name="{{ $status }}">
							
								<option value="">Select</option>
								<!-- count($selected_IS_tags) -->
								@if ( isset($selected_IS_tags) )
									@foreach (json_decode($selected_IS_tags) as $id=>$name )
										<option value="{{ $id }}" @if( @$temp_tag_settings[$status] ==$id ) selected="selected" @endif>{{ $name }}</option>
									@endforeach
								@endif
							
						</select>
						<span class="role-error" style="color:red; display:none;">Please select a value</span>
					</div>
				</div>
			</div>
		@endforeach
	@endif
    <div class="row">
		<div class="col-lg-9">
			&nbsp;
		</div>
		<div class="col-lg-3"><br/>
			<button class="btn btn-success btn-block" onclick="saveSelections($(this))">Save Tags <i class="fa"></i></button>
		</div>
	</div>

	<hr/>

	<div id="pseudoFrmMdditionalOption">
		<div class="row temp-detail-checkbox-row">
			<div class="col-lg-12">
				<h3><input type="checkbox" id="chkboxSaveDocFields" name="saveDocFields" value="1"/> <label for="chkboxSaveDocFields">Save Doc Fields After Sending (optional)</label></h3>
			</div>
		</div>

		<div class="save-doc-wrapper hide animated">
			<div class="form-group">
				<div class="row">
					<div class="col-lg-3">
						&nbsp;
					</div>
					<div class="col-lg-4 text-center">
						Contact Field
					</div>
					<div class="col-lg-4 text-center">
						Opportunity Field
					</div>
				</div>
			</div>
		
			<div class="form-group">
				<div class="row">
					<div class="col-lg-3">
						Document Sent Date
					</div>
					<div class="col-lg-4">
						<select name="infsField[document_sent_date][contact_field]" class="form-control dropdownSaveDoc" >
							<option value="">Select...</option>
							@foreach($infsFields as $infsField)
								@if($infsField['FormId'] == -1)
									<option {{isset($documentAdditionalInfsFieldsDocSentDate['contact_field']) && $documentAdditionalInfsFieldsDocSentDate['contact_field'] == $infsField['Value'] ? 'selected' : ''}} value="{{$infsField['Value']}}">{{$infsField['Name']}}</option>
								@endif
							@endforeach
						</select>
					</div>
					<div class="col-lg-4">
						<select name="infsField[document_sent_date][opportunity_field]" class="form-control dropdownSaveDoc" >
							<option  value="">Select...</option>
							@foreach($infsFields as $infsField)
								@if($infsField['FormId'] == -4)
									<option {{isset($documentAdditionalInfsFieldsDocSentDate['opportunity_field']) && $documentAdditionalInfsFieldsDocSentDate['opportunity_field'] == $infsField['Value'] ? 'selected' : ''}} value="{{$infsField['Value']}}">{{$infsField['Name']}}</option>
								@endif
							@endforeach
						</select>
					</div>
				</div>
			</div>

			<div class="form-group">
				<div class="row">
					<div class="col-lg-3">
						Total Document Revenue
					</div>
					<div class="col-lg-4">
						<select name="infsField[total_document_revenue][contact_field]" class="form-control dropdownSaveDoc" >
							<option value="">Select...</option>
							@foreach($infsFields as $infsField)
								@if($infsField['FormId'] == -1)
									<option {{isset($documentAdditionalInfsFieldsDocRevenue['contact_field']) && $documentAdditionalInfsFieldsDocRevenue['contact_field'] == $infsField['Value'] ? 'selected' : ''}}  value="{{$infsField['Value']}}">{{$infsField['Name']}}</option>
								@endif
							@endforeach
						</select>
					</div>
					<div class="col-lg-4">
						<select name="infsField[total_document_revenue][opportunity_field]" class="form-control dropdownSaveDoc" >
							<option value="">Select...</option>
							@foreach($infsFields as $infsField)
								@if($infsField['FormId'] == -4)
									<option {{isset($documentAdditionalInfsFieldsDocRevenue['opportunity_field']) && $documentAdditionalInfsFieldsDocRevenue['opportunity_field'] == $infsField['Value'] ? 'selected' : ''}} value="{{$infsField['Value']}}">{{$infsField['Name']}}</option>
								@endif
							@endforeach
						</select>
					</div>
				</div>
			</div>
		</div>
		
		<div class="row temp-detail-checkbox-row">
			<div class="col-lg-12">
				<h3><input type="checkbox" id="chkboxMostMostRecentOpportunity" name="mostMostRecentOpportunity" value="1"/> <label for="chkboxMostMostRecentOpportunity"> Move Most Recent Opportunity Based on Status (optional)</label></h3>
			</div>
		</div>
	
		<div class="most-recent-opp-wrapper hide animated">
			<div class="form-group">
				<div class="row">
					
					<div class="col-lg-6">
						<input type="checkbox" {{ $createNewOppIfNotExists ? 'checked' : ''}} name="create_opp_if_not_exists" id="chkboxCreateOpp" value="1" />
						<label for="chkboxCreateOpp" class="nobold">Create Opportunity if one does not exist?</label>
					</div>
				</div>
			</div>

			@if ( count($document_status) )
                @foreach($document_status as $status )
                    <div class="form-group">
                        <div class="row">
                            <div class="col-lg-3">
                                {{ $status }}
                            </div>
                            <div class="col-lg-6">
                                <select name="stage_status[{{ $status }}]" required class="form-control dropdownMoveMostRecentOpportunity" >
                                    <option value="">Select...</option>
                                    @foreach($stages as $stage)
                                        <option {{isset($documentStageSettings[$status]) && $documentStageSettings[$status] == $stage['Id'] ? 'selected' : ''}} value="{{$stage['Id']}}">{{$stage['StageName']}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
			
		</div>

		<div class="form-group addtional-option-btn-wrapper hide">
			<div class="row">
				<div class="col-lg-3 col-md-offset-9 text-right">
					<button class="btn btn-success btn-block btnSaveOptionals">Save <i class="fa"></i></button>
				</div>
			</div>
		</div>
	</div>
	
	<hr/>



	<div class="row">
		<div class="col-lg-12">
		
			<h3>Quick HTTP POST Setup Instructions</h3>
			<p>To send your document, you need to match your Infusionsoft data with the tokens and fields in your document. To do this, you use a HTTP post element inside a campaign.</p>
			<ol class="temapletol">
				<li>
					<h4>Setup Your HTTP Post In Your Campaign Where You Want To Trigger Your Document Send</h4>
					<p>Add a HTTP post to your campaign with the following configuration.</p>
					
					<div class="row">
						<div class="col-lg-11 col-lg-offset-1 "><h5><strong>POST URL</strong> (copy and paste this)</h5></div>
						<div class="col-lg-8 col-lg-offset-1"><input  class="form-control bg-color" style="width: 100%;" name="Posturl" type="text"  value="{{ url('tools/panda') }}" readonly></div> <div class="col-lg-3">	</div>
					</div>
					<div class="form-group">
					<div class="row">
						<div class="col-lg-11 col-lg-offset-1 "><p>Enter the values on the left into your http post (copy / paste):</p><h5><strong>Name/ Value Pairs</strong></h5></div>
					</div>
					</div>
					<div class="form-group">
						<div class="row">
							<div class="col-lg-4 col-lg-offset-1">
								<input class="form-control bg-color" value="FuseKey" readonly>
								<span style="float: right;font-size: 9px;color: black;">required</span>
							</div>
							<div class="col-lg-1" align="center">
								=
							</div>
							<div class="col-lg-4">
								<input class="form-control bg-color" value="{{ \Auth::user()->FuseKey }}" readonly>
							</div>
						</div>
					</div>
					<div class="form-group">
						<div class="row">
							<div class="col-lg-4 col-lg-offset-1">
								<input class="form-control bg-color" value="app" readonly>
								<span style="float: right;font-size: 9px;color: black;">required</span>
							</div>
							<div class="col-lg-1" align="center">
								=
							</div>
							<div class="col-lg-4">
								<input class="form-control bg-color" value="" readonly id="appName">
							</div>
						</div>
					</div>

					<div class="form-group">
						<div class="row">
							<div class="col-lg-4 col-lg-offset-1">
								<input class="form-control bg-color" value="TemplateID" readonly>
								<span style="float: right;font-size: 9px;color: black;">required</span>
							</div>
							<div class="col-lg-1" align="center"> = </div>
							<div class="col-lg-4">
								<input class="form-control bg-color" value="{{ @$templateID }}" id="TemplateID" readonly >
							</div>

						</div>
					</div>

					<div class="form-group">
						<div class="row">
							<div class="col-lg-4 col-lg-offset-1">
								<input class="form-control bg-color" value="contactId" readonly>
								<span style="float: right;font-size: 9px;color: black;">required</span>
							</div>
							<div class="col-lg-1" align="center"> = </div>
							<div class="col-lg-4">
								<input class="form-control bg-color" value="~Contact.Id~" id="contactId" readonly >
							</div>

						</div>
					</div>

                    <div class="form-group">
						<div class="row">
							<div class="col-lg-4 col-lg-offset-1">
								<input class="form-control bg-color" value="doc_name" readonly>
							</div>
							<div class="col-lg-1" align="center"> = </div>
							<div class="col-lg-4">
							    Name of the document to be sent (IE. ~Company.Name~ Sales Proposal)
							</div>

						</div>
					</div>
					
					<div class="form-group">
						<div class="row">
							<div class="col-lg-4 col-lg-offset-1">
								<input class="form-control bg-color" value="doc_message" readonly>
							</div>
							<div class="col-lg-1" align="center"> = </div>
							<div class="col-lg-4">
							    Message To Be Sent With The Document (otherwise blank)
							</div>

						</div>
					</div>
				
					<div class="form-group">
						<div class="row">
							<div class="col-lg-4 col-lg-offset-1">
								<input class="form-control bg-color" value="Status" readonly>
								<span style="float: right;font-size: 9px;color: black;">required</span>
							</div>
							<div class="col-lg-1" align="center"> = </div>
							<div class="col-lg-4">1 = send immediately, 0 = create a draft, don't send
							
							</div>

						</div>
					</div>
					
					<div class="form-group">
						<div class="row">
							<div class="col-lg-4 col-lg-offset-1">
								<input class="form-control bg-color" value="PricingTable" readonly>
								<span style="float: right;font-size: 9px;color: black;">required</span>
							</div>
							<div class="col-lg-1" align="center"> = </div>
							<div class="col-lg-4">
								1 = get pricing from opportunity, 0 = ignore
							</div>

						</div>
					</div>
					    @php
					        $i = 0;
					    @endphp
					@if ($tokens)
						@foreach ( $tokens as $token )
							<div class="form-group">
								<div class="row">
									<div class="col-lg-4 col-lg-offset-1 @if ( strpos($token, ' ') !== false ) red-marked  @php $i=1; @endphp @endif" @if ( strpos($token, '.Email') !== false || strpos($token, '.FirstName') !== false || strpos($token, '.LastName') !== false ) readonly @endif>
										<input class="form-control bg-color" value="{{ $token }}">
										@if ( strpos($token, '.Email') !== false || strpos($token, '.FirstName') !== false || strpos($token, '.LastName') !== false ) <span style="float: right;font-size: 9px;color: black;">required</span> @endif
									</div>
									<div class="col-lg-1" align="center">
										=
									</div>
									<div class="col-lg-4">
                                        Use Infusionsoft Merge Function To Merge Contact OR Owner Data.
									</div>
								</div>
							</div>
						@if( $i == 1 )
						    <span style="color:red;">Error : Tokens cannot contain a space - remove it inside Pandadoc, or replace it with an underscore. Then refresh this page.</span>
						@php $i=0; @endphp
						@endif
						@endforeach
					@endif
					@if ($fields)

						@foreach ( $fields as $field )
							<div class="form-group">
								<div class="row">
									<div class="col-lg-4 col-lg-offset-1 @if ( strpos($field, '.') !== false || strpos($field, ' ') !== false ) red-marked  @php $i=1; @endphp @endif" >
										<input class="form-control bg-color" value="{{ $field }}">
										@if ( in_array( $field,$duplicate_fields )  )
											<br>
											<span style="color:red;">Field's name must be unique.</span>
										@endif
									</div>
									<div class="col-lg-1" align="center">
										=
									</div>
									<div class="col-lg-4">
                                        Use Infusionsoft Merge Function To Merge Contact OR Owner Data.
									</div>
								</div>
							</div>
						@if( $i == 1 )
						    <span style="color:red;">Error : Fields cannot contain a period (.) or space - remove it inside Pandadoc. Then refresh this page.</span>
						@php $i=0; @endphp
						@endif
						@endforeach

					@endif
					<div class="row">
					<div style=" width: 20%; float: right;">

					</div>
					</div>

				</li>

				<li>
					<h4>Add Your Infusionsoft Contact & Owner Merge Fields</h4>
					<p>Now, you have added all your PandaDoc fields, you need to input either a fixed value (IE. John), or more likely, insert an infusionsoft merge field into the box next to it.</p>
					
					<p>We get the data from the contact (or contact owner) and pass it into the corresponding PandaDoc field when we make your document.</p>

						<div class="row">
							<div class="col-lg-5 col-lg-offset-1 "><h5><strong>FOR EXAMPLE: This PandaDoc Field/Token... </strong></h5></div><div class="col-lg-4 col-lg-offset-1 "><h5><strong>Will Get This Data..</strong></h5></div>
						</div>


					<div class="form-group">
						<div class="row">
							<div class="col-lg-3 col-lg-offset-1">
								<input class="form-control bg-color" name="clientfirstname" type="text" value="SalesRep.FirstName" readonly>
							</div>
							<div class="col-lg-1"  align="center">
								=
							</div>
							<div class="col-lg-4">
								<input class="form-control bg-color" name="contactfirstname" type="text" value="~Owner.FirstName~" readonly>
							</div>
						</div>
					</div>
					<div class="form-group">
						<div class="row">
							<div class="col-lg-3 col-lg-offset-1">
								<input class="form-control bg-color" name="clientfirstname" type="text" value="SalesRep.LastName" readonly>
							</div>
							<div class="col-lg-1"  align="center">
								=
							</div>
							<div class="col-lg-4">
								<input class="form-control bg-color" name="contactfirstname" type="text" value="~Owner.LastName~" readonly>
							</div>
						</div>
					</div>
					
					<div class="form-group">
						<div class="row">
							<div class="col-lg-3 col-lg-offset-1">
								<input class="form-control bg-color" name="clientfirstname" type="text" value="SalesRep.Email" readonly>
							</div>
							<div class="col-lg-1"  align="center">
								=
							</div>
							<div class="col-lg-4">
								<input class="form-control bg-color" name="contactfirstname" type="text" value="~Owner.Email~" readonly>
							</div>
						</div>
					</div>
					<div class="form-group">
						<div class="row">
							<div class="col-lg-3 col-lg-offset-1"><input class="form-control bg-color" name="clientfirstname" type="text" value="Client.FirstName" readonly></div><div class="col-lg-1"  align="center"> = </div> <div class="col-lg-4"><input class="form-control bg-color" name="contactfirstname" type="text" value="~Contact.FirstName~" readonly></div>
						</div>
					</div>

					<div class="form-group">
						<div class="row">
							<div class="col-lg-3 col-lg-offset-1"><input class="form-control bg-color" name="clientfirstname" type="text" value="Client.LastName"readonly></div><div class="col-lg-1"  align="center"> = </div> <div class="col-lg-4"><input class="form-control bg-color" name="contactfirstname" type="text" value="~Contact.LastName~" readonly></div>
						</div>
					</div>

					<div class="form-group">
						<div class="row">
							<div class="col-lg-3 col-lg-offset-1"><input class="form-control bg-color" name="clientfirstname" type="text" value="Client.Email" readonly></div><div class="col-lg-1"  align="center"> = </div> <div class="col-lg-4"><input class="form-control bg-color" name="contactfirstname" type="text" value="~Contact.Email~" readonly></div>
						</div>
					</div>

					<div class="row">
						<div class="col-lg-12">
							<p>You can enter contact data and fields using the merge fields, or you can enter something static, such as price or sales person name.</p>

							<p>An issue that we commonly come across here is that the PandaDoc field names are not descriptive enough for you to know what field
								you are sending the data into - ie. the field name is field1 and you don't know whether that is the name field or email field etc.</p>

							<p>The easy solution is for you to set these names in your PandaDoc proposal. Here is how you do that:</p>
							<p align="center"><img src="{{ url('assets/images/managetemplate_11.jpg') }}" class="img-responsive"/></p>
							<p>If you make these changes, just to save your template and refresh this page and the fields below will change.</p>
						</div>
					</div>

				</li>
                <li>
                    <h3>Setting Up Your Campaign To Trigger Your Proposal</h3>
        			<p>In order to trigger the sending of a proposal from InfusionSoft, we use what is called a HTTP POST element that goes inside a sequence.</p>
        			<p>Your campaign will look something like this:</p>
        			<p><img src="{{ url('assets/images/managetemplate_03.jpg') }}" class="img-responsive"/></p>
        			<p>Inside the sequence, you will setup like this:</p>
        			<p><img src="{{ url('assets/images/managetemplate_07.jpg') }}" class="img-responsive"/></p>
        			<p>NOTE: The timer and error notification act as a form of  redundancy. When the propsal is successfully sent, we TAG the user as having sent it
        				which stops the sequence. If for whaterver reason it DOESNT send, the tag won’t be applied and this task will notify you.</p>
				</li>
				<li>
					<h4>Save, Publish & Test</h4>
					<p>Now that you have entered all your merge fields, it is time to save your HTTP POST.</p>
					<p>Then you will want to publish your campaign and apply your trigger to test contact - the test contact will get the proposal imediately.</p>
				</li>

			</ol>
		</div>
	</div>

</div>
</form>

<script>
	$('.dropdownSaveDoc').selectize();
</script>