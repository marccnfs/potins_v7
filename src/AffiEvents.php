<?php


namespace App;


final class AffiEvents
{
    const CONFIRM_RESA='affi.confirm_resa';
    const REGISTRATION_SUCCESS="affi.registration_success";
    const REGISTRATION_INITIALIZE="affi.registration_initialize";
    const RESETTING_RESET_INITIALIZE="affi.resetting_reset_initialize";
    const RESETTING_RESET_SUCCESS="affi.resetting_reset_success";
    const RESETTING_RESET_REQUEST='affi.resetting_reset_request';
    const REGISTRATION_COMPLETED="affi.registration_completed";
    const REGISTRATION_FAILURE="affi.registration_failure";
    const REGISTRATION_CONFIRMED ="affi.registration_confirmed";
    const RESETTING_RESET_COMPLETED ="affi.resetting_reset_completed";
    const SECURITY_IMPLICIT_LOGIN ="affi.security_implicit_login";
    const PROFILE_EDIT_COMPLETED ='affi.edit_completed';
    const REGISTRATION_CONFIRM="affi.registration_confirm";
    const ACTIVATION_SUCCESS="affi.activation_success";
    const COMMANDE_SUCCESS="affi.commande_success";
    const ADD_REGISTRATION_SUCCESS="affi.add_registration_success";
    const ADD_DISPATCH_SUCCESS="affi.add_dispatch_success";
    const ADD_CONTACT_SUCCESS="affi.add_contact_success";
    const ADD_CLIENT_SUCCESS="affi.add_client_success";
    const DISPATCH_REGISTRATION_SUCCESS="affi.dispatch_success";
    const DISPATCH_INVIT_WEBSITE="affi.invit_dispatch_success";
    const RESETTING_SEND_EMAIL_INITIALIZE="affi.ressting_mail_init";
    const RESETTING_SEND_EMAIL_CONFIRM="affi.ressting_mail_confirm";
    const RESETTING_SEND_EMAIL_COMPLETED="affi.ressting_mail_completed";
    const CHANGE_PASSWORD_INITIALIZE ='affi.password_initialize';
    const CHANGE_PASSWORD_COMPLETED ='affi.password_completed';
    const CHANGE_PASSWORD_SUCCESS ='affi.password_succes';
    const CHANGE_PASSWORD_REQUEST = 'affi.password_request';
    const CHANGE_PASSWORD_TEST = 'affi.password_test';
    const CREATE_WEBSITE = "affi.website.create";
    const NOTIFICATION_NEW_MESSAGE="affi.notif_newmessage";
    const NOTIFICATION_ADD_COMMENT="affi.notif_addmessage";
    const NOTIFICATION_NEW_POST="affi.notif_newpost";
    const NOTIFICATION_NEW_OFFRE="affi.notif_newoffre";
    const NOTIFICATION_NEW_MODULE="affi.notif_newmodule";
    const NOTIFICATION_CONTACT_NEW_MESSAGE="affi.notif_newmessage_contact";
    const NOTIFICATION_ADD_COMMENT_POST_DISPATCH="affi.notif_commentpost_dispatch";
    const NOTIFICATION_ADD_COMMENT_POST_CONTACT="affi.notif_commentpost_contact";
    const NOTIFICATION_ADD_COMMENT_OFFRE_DISPATCH="affi.notif_commentoffre_dispatch";
    const NOTIFICATION_ADD_COMMENT_OFFRE_CONTACT="affi.notif_commentoffre_contact";
    const NOTIFICATION_ADD_MSG_WEBSITE_DISPATCH="affi.notif_msgwebsite_dispatch";
    const NOTIFICATION_ADD_MSG_WEBSITE_CONTACT="affi.notif_msgwebsite_contact";
    const NOTIFICATION_ANSWER_COMMENT_POST_DISPATCH="affi.notif_answer-commentpost_dispatch";
    const NOTIFICATION_ANSWER_COMMENT_POST_CONTACT="affi.notif_answer-commentpost_contact";
    const NOTIFICATION_ANSWER_COMMENT_OFFRE_DISPATCH="affi.notif_answer-commentoffre_dispatch";
    const NOTIFICATION_ANSWER_COMMENT_OFFRE_CONTACT="affi.notif_answer-commentoffre_contact";
    const NOTIFICATION_ANSWER_MSG_WEBSITE_DISPATCH="affi.notif-answer-website-disptach";
    const NOTIFICATION_ANSWER_MSG_WEBSITE_CONTACT="affi.notif-answer-webdsite-contact";
    const SHOW_WEBSITE="aff.log_show_website";
    const INVIT_TOADMIN_BYMAIL="aff.invt-mail-tobe-admin";
}