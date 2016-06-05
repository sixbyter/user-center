Click here to reset your password: <a href="{{ $link = url('password/reset/email?'.'token='.$token.'&email='.$user->getEmailForPasswordReset()) }}"> {{ $link }} </a>
