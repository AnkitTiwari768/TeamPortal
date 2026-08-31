<?php return array(
  'enable_two_factor_authentication' => '1',
  'enable_captcha' => '1',
  'enable_otp_email_notification' => '1',
  'enable_email_notification_register' => '1',
  'enable_email_notification_application_submission' => '0',
  'enable_otp_mobile_notification' => '0',
  'enable_country_module' => '1',
  'enable_state_module' => '1',
  'enable_district_module' => '1',
  'enable_block_module' => '1',
  'enable_hierarchical_list' => '1',
  'send_mail_to_liaison' => '1',
  'send_mail_to_CC' => ['unee.php@gmail.com'],
  'enable_revert_email_applicant_notification' => '1',
  'enable_approve_email_from_mha' => '1',
  'enable_approve_email_from_legal' => '1',
  'enable_approve_reject_email_from_mib' => '1',
  'enable_revert_email_to_ffo' => '1',

  /**
   * Team Portal Settings
   */
  'enable_batch_monthly_apply_validation' => false,
  'pass_udyam_api' => true,



  'helpdesk_number' => '14475 / +91 74287-79111',
  'helpdesk_email' => 'team@msmemart.com',
  'udyam_api_by_pass' => false,
  'udyam_api_by_pass_udyam_bharat_portal' => false,

  /**
   * Udyam API cURL timeouts (seconds), used by the MSE bulk draft cron.
   *
   * connect_timeout was previously 1s, which turned ordinary network latency
   * into "API not responding" and burned retry attempts on healthy records.
   * timeout was 300s, long enough for a single stalled record to run the whole
   * cron past max_execution_time and leave the rest of the batch Pending.
   */
  'udyam_api_connect_timeout' => 10,
  'udyam_api_timeout' => 60,


  /** 
   * Workshop Settings
   */
  'financial_year_start_year' => 2022,


);
