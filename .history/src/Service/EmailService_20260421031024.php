
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EmailService
{
    public function __construct(private MailerInterface $mailer) {}

    public function sendOtp(string $to, string $code)
    {
        $email = (new Email())
            ->from('zidisamir993@gmail.com')
            ->to($to)
            ->subject('Your OTP Code')
            ->html("<h1>Your code: $code</h1>");

        $this->mailer->send($email);
    }
}