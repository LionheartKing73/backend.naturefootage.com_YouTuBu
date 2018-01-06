<?php
/**
 * SAML 2.0 remote SP metadata for simpleSAMLphp.
 *
 * See: https://simplesamlphp.org/docs/stable/simplesamlphp-reference-sp-remote
 */

///*
// * Example simpleSAMLphp SAML 2.0 SP
// */
//$metadata['https://saml2sp.example.org'] = array(
//	'AssertionConsumerService' => 'https://saml2sp.example.org/simplesaml/module.php/saml/sp/saml2-acs.php/default-sp',
//	'SingleLogoutService' => 'https://saml2sp.example.org/simplesaml/module.php/saml/sp/saml2-logout.php/default-sp',
//);
//
///*
// * This example shows an example config that works with Google Apps for education.
// * What is important is that you have an attribute in your IdP that maps to the local part of the email address
// * at Google Apps. In example, if your google account is foo.com, and you have a user that has an email john@foo.com, then you
// * must set the simplesaml.nameidattribute to be the name of an attribute that for this user has the value of 'john'.
// */
//$metadata['google.com'] = array(
//	'AssertionConsumerService' => 'https://www.google.com/a/g.feide.no/acs',
//	'NameIDFormat' => 'urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress',
//	'simplesaml.nameidattribute' => 'uid',
//	'simplesaml.attributes' => FALSE,
//);

$metadata['https://ec2-54-144-244-190.compute-1.amazonaws.com/auth/saml/metadata'] = array(
    'AssertionConsumerService' => array(
        array(
            'index' => 0,
            'isDefault' => TRUE,
            'Location' => 'https://ec2-54-144-244-190.compute-1.amazonaws.com/auth/saml/callback',
            'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST'
        ),
    ),
    'NameIDFormat' => 'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified',
);
