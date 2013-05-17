
# HMRC RTI

PHP to work with the HMRC API for Real Time Information (PAYE).

http://www.hmrc.gov.uk/softwaredevelopers/rti/index.htm

---

## Testing

Register with HMRC to test:

http://www.hmrc.gov.uk/softwaredevelopers/register.htm

Possibly a local test service:

http://www.hmrc.gov.uk/softwaredevelopers/lts-techpack.htm

---

Initially I found some basic details about a SOAP/HTTP method:

http://www.hmrc.gov.uk/softwaredevelopers/paye/internet/dps.htm

http://www.hmrc.gov.uk/softwaredevelopers/paye/internet/dps-api.pdf

But no mentions about the WSDL file... although there is a download (does not seem to work though):

http://www.hmrc.gov.uk/softwaredevelopers/paye/internet/dps.htm

http://www.hmrc.gov.uk/ebu/paye_techpack/dps-wsdl.zip

Then there is some kind of "Document Submission Protocol", seems to be HTTP POST with XML:

http://www.hmrc.gov.uk/schemas/GatewayDocumentSubmissionProtocol_V3.1.pdf

So are we posting some custom XML data?

http://www.hmrc.gov.uk/softwaredevelopers/rti/internet-rti.htm

http://www.hmrc.gov.uk/softwaredevelopers/rti/rti-howtouse-13-14.pdf

And lots of talk about some kind of "RTI hash" (SHA-256 based)

http://www.hmrc.gov.uk/rti/cross-reference.pdf

http://www.hmrc.gov.uk/softwaredevelopers/rti/developerfaqs.htm
