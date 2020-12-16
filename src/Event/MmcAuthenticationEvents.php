<?php

namespace Mmc\Security\Event;

final class MmcAuthenticationEvents
{
    const AUTHENTICATION_SUCCESS = 'security.mmc.authentication.success';

    const AUTHENTICATION_INTERACTIVE_SUCCESS = 'security.mmc.authentication.interactive_success';

    const LOGOUT_SUCCESS = 'security.mmc.logout.success';

    const AUTHENTICATION_CHANGE_PASSWORD = 'security.mmc.authentication.change_password';

    const AUTHENTICATION_CHANGE_USERNAME = 'security.mmc.authentication.change_username';

    const AUTHENTICATION_REFRESH_TOKEN_BY_EMAIL = 'security.mmc.authentication.refresh_token_by_email';

    const AUTHENTICATION_TOKEN_BY_EMAIL_HAS_BEEN_VERIFIED = 'security.mmc.authentication.token_by_email_has_been_verified';

    const AUTHENTICATION_CREATE_EMAIL = 'security.mmc.authentication.create_email';

    const AUTHENTICATION_CHANGE_EMAIL = 'security.mmc.authentication.change_email';
}
