# Bugs, issues, quirks in Mailchimp's API

I thought it would be useful to document these.

## No sandbox

If Mailchimp offered a sandbox account - that worked like a normal one
except for actually sending mail, development would be a lot easier. But
instead, everything must be done with real email addresses and real
paid-for services. This has several risks and pitfalls: accidents can be
costly and testing email addresses can trigger security alerts
("foo@example.com" has subscirbed too much recently, request denied). Data
cannot be properly deleted; if you delete an email address it is *stored*
as deleted for the purposes of remembering to never use it again. This
means you need an infinite source of dummy addresses for testing.

## Member IDs

These are supposed to be `md5(strtolower("contact@example.com"))`, however if
you have a very old list (not entirely sure, possibly pre-2015) then you may
have invalid IDs because mailchimp set the id without lower-casing it first,
and now you can't change that so you have no way with the API to make updates.

Also, there's the issue that lowercase is (a) destructive because the localpart
of the email address can be case-sensitive and (b) has different
implementations based on character sets (`strtolower` vs `mb_strtolower` for
example).

## Incomplete API documentation.

e.g. the `PUT lists/{listID}/members/{memberID}` method is completely
undocumented.

The documentation is at
https://mailchimp.com/developer/reference/lists/list-members/

This page mentions PUT
https://mailchimp.com/developer/guides/get-started-with-mailchimp-api-3/#_HTTP_Methods

This page has a section about create/update but does not mention PUT!
https://mailchimp.com/developer/guides/manage-subscribers-with-the-mailchimp-api/#Create_or_Update_a_Contact

Over chat I received this:

> I checked with the team and from my understanding that `PUT /lists/{list_id}/members/{subscriber_hash}` should work as it has, but looks like the document was removed - I do't know if that was intentional or not but I did pass along some feedback on that

## Batch update API

You can download responses to batch updates by a request to a special URL.

This downloads a gzipped tar file. However the format of the tar file is incompatible with PHP's phar class (which normally opens tar files OK) since the tar file begins with defining the current directory `./`

I believe it should start with either no directory, or a named relative one, like `response/`.

This means that I had to write a custom class to handle this tar format.

## Fetch since

If you set a very old date for fetching members updated 'since', e.g.
1970, the API seems to just hang, even with tiny lists that don't have
much history.
