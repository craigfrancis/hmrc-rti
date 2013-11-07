
# HMRC RTI

PHP to work with the HMRC API for Real Time Information (PAYE).

You will need to register with HMRC to test:

http://www.hmrc.gov.uk/softwaredevelopers/register.htm

---

## Documentation

http://www.hmrc.gov.uk/softwaredevelopers/index.htm

http://www.hmrc.gov.uk/softwaredevelopers/rti/index.htm

Start with the "Document Submission Protocol", which allows you to talk to many of the HMRC services:

http://www.hmrc.gov.uk/schemas/GatewayDocumentSubmissionProtocol_V3.1.pdf

Then look at the 5 types of submission for RTI:

- Full Payment Submission
- Employer Payment Summary
- Employer Alignment Submission
- NINO Verification Request
- Earlier Year Update

http://www.hmrc.gov.uk/softwaredevelopers/rti/internet-rti.htm

http://www.hmrc.gov.uk/softwaredevelopers/rti/rti-howtouse.pdf

http://www.hmrc.gov.uk/softwaredevelopers/rti/rti-howtouse-13-14.pdf

---

## Testing

The Government Gateway has a test service at:

	https://secure.dev.gateway.gov.uk/submission
	https://secure.dev.gateway.gov.uk/submissionTPVS

	https://www.tpvs.hmrc.gov.uk/HMRC/RTIFPS
	https://www.tpvs.hmrc.gov.uk/HMRC/RTIEPS
	https://www.tpvs.hmrc.gov.uk/HMRC/RTIEAS
	https://www.tpvs.hmrc.gov.uk/HMRC/RTINVR
	https://www.tpvs.hmrc.gov.uk/HMRC/RTIEYU

---

## Live API

https://secure.gateway.gov.uk/submission

## RTI Submission types

There are 5 types of submissions available for RTI:

	HMRC-PAYE-RTI-EAS - Employer Alignment Submission
	HMRC-PAYE-RTI-EPS - Employer Payment Summary
	HMRC-PAYE-RTI-EYU - Earlier Year Update
	HMRC-PAYE-RTI-FPS - Full Payment Submission
	HMRC-PAYE-RTI-NVR - NINO Verification Request

**Employer Alignment Submission (EAS):** allows employers and HMRC to align employee records before the employer joins RTI.

**Full Payment Submission (FPS):** required each time an employer makes a payment to an employee. Can be used to report the final return for year details.

**Employer Payment Summary (EPS):** the submission will include data to enable HMRC to calculate employer liability. The submission will only be needed where the employer needs to notify HMRC of adjustments to their overall liability. Can be used to report the final return for year details. Should also be used to report if no employees have been paid in a pay period.

**NINO Verification Request (NVR):** allows employers to validate the NINO or check if a new employee NINO is available.

**Earlier Year Update (EYU):** allows employers to correct, after 19 April, any of the year to date totals submitted in their most recent FPS for a previous tax year. This only applies to RTI years and the first year an employer can amend using an EYU is 2012/2013.

The figures used in an EYU will be added to the amounts already reported so only the differences between the amounts should be entered (delta figures). To reduce a previously reported amount, a negative figure should be entered.

---

## RTI hash

Only needed if paying employees by BACS:

http://www.hmrc.gov.uk/rti/cross-reference.pdf

http://www.hmrc.gov.uk/softwaredevelopers/rti/developerfaqs.htm

---

## Alternatives

https://code.google.com/p/php-govtalk/
