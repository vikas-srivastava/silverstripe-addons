<div class="typography">
	<% include TopBar %>
	<div id="content">
		<h2>
			<% if BasePageLink %><a href="$BasePageLink"><% end_if %>
			<% if TitleImage %><img src="$TitleImage.URL" alt="<% if TitleImageAlt %>$TitleImageAlt<% else %>$Title<% end_if %>">
			<% else %>$Title<% end_if %>
			<% if BasePageLink %></a><% end_if %>
		</h2>
	
		$Content
		$Form
	</div>	
</div>
