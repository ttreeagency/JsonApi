<?php

namespace Ttree\JsonApi\Contract\Parameters;

interface EncodingParametersInterface
{
    /** Message */
    public const MSG_ERR_INVALID_PARAMETER = 'Invalid Parameter.';

    public function getSorts(): iterable;


}