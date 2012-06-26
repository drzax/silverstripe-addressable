<% if Address1 %>
<div itemscope itemtype="schema.org/PostalAddress">

<% if Address1 %><span class="Address1" itemprop="streetAddress">$Address1</span>,<br><% end_if %>
<% if Address2 %><span class="Address2" itemprop="streetAddress">$Address2</span>,<br><% end_if %>
<% if City %><span class="AddressCity" itemprop="addressLocality">$City</span>,<br><% end_if %>
<% if Region %><span class="AddressRegion" itemprop="addressRegion">$Region</span>,<br><% end_if %>
<% if Postcode %><span class="AddressPostcode" itemprop="postalCode">$Postcode</span><br><% end_if %>
<% if Country %><span class="AddressCountry" itemprop="addressCountry">$CountryName</span><% end_if %>

</div>
<% end_if %>