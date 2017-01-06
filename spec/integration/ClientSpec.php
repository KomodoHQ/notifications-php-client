<?php

namespace spec\integration\Alphagov\Notifications;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

use Alphagov\Notifications\Authentication\JWTAuthenticationInterface;
use Alphagov\Notifications\Client;
use Alphagov\Notifications\Exception\UnexpectedValueException;

use GuzzleHttp\Psr7\Uri;
use Http\Client\HttpClient as HttpClientInterface;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;

/**
 * Integration Tests for the PHP Notify Client.
 *
 *
 * Class ClientSpec
 * @package spec\Alphagov\Notifications
 */
class ClientSpec extends ObjectBehavior
{
    private static $notificationId;

    function let(){

        $this->beConstructedWith([
            'baseUrl'       => getenv('NOTIFY_API_URL'),
            'serviceId'     => getenv('SERVICE_ID'),
            'apiKey'        => getenv('API_KEY'),
            'httpClient'    => new \Http\Adapter\Guzzle6\Client
        ]);

    }

    function it_is_initializable(){
        $this->shouldHaveType('Alphagov\Notifications\Client');
    }

    function it_receives_the_expected_response_when_sending_an_email_notification(){

        $response = $this->sendEmail( getenv('FUNCTIONAL_TEST_EMAIL'), getenv('EMAIL_TEMPLATE_ID'), [
            "name" => "Foo"
        ]);

        $response->shouldBeArray();
        $response->shouldHaveKey( 'id' );
        $response['id']->shouldBeString();

        $response->shouldHaveKey( 'reference' );

        $response->shouldHaveKey( 'content' );
        $response['content']->shouldBeArray();
        $response['content']->shouldHaveKey( 'from_email' );
        $response['content']['from_email']->shouldBeString();
        $response['content']->shouldHaveKey( 'body' );
        $response['content']['body']->shouldBeString();
        $response['content']['body']->shouldBe("Hello Foo\n\nFunctional test help make our world a better place");
        $response['content']->shouldHaveKey( 'subject' );
        $response['content']['subject']->shouldBeString();
        $response['content']['subject']->shouldBe("Functional Tests are good");

        $response->shouldHaveKey( 'template' );
        $response['template']->shouldBeArray();
        $response['template']->shouldHaveKey( 'id' );
        $response['template']['id']->shouldBeString();
        $response['template']->shouldHaveKey( 'version' );
        $response['template']['version']->shouldBeInteger();
        $response['template']->shouldHaveKey( 'uri' );

        $response->shouldHaveKey( 'uri' );
        $response['uri']->shouldBeString();

        self::$notificationId = $response['id']->getWrappedObject();

    }

    function it_receives_the_expected_response_when_looking_up_an_email_notification() {

      // Requires the 'it_receives_the_expected_response_when_sending_an_email_notification' test to have completed successfully
      if(is_null(self::$notificationId)) {
          throw new UnexpectedValueException('Notification ID not set');
      }

      $notificationId = self::$notificationId;

      // Retrieve email notification by id and verify contents
      $response = $this->getNotification($notificationId);
      $response->shouldBeArray();
      $response->shouldHaveKey( 'id' );
      $response['id']->shouldBeString();

      $response->shouldHaveKey( 'body' );
      $response['body']->shouldBeString();
      $response['body']->shouldBe("Hello Foo\n\nFunctional test help make our world a better place");

      $response->shouldHaveKey( 'subject' );
      $response->shouldHaveKey( 'reference' );
      $response->shouldHaveKey( 'email_address' );
      $response['email_address']->shouldBeString();
      $response->shouldHaveKey( 'phone_number' );
      $response->shouldHaveKey( 'line_1' );
      $response->shouldHaveKey( 'line_2' );
      $response->shouldHaveKey( 'line_3' );
      $response->shouldHaveKey( 'line_4' );
      $response->shouldHaveKey( 'line_5' );
      $response->shouldHaveKey( 'line_6' );
      $response->shouldHaveKey( 'postcode' );
      $response->shouldHaveKey( 'type' );
      $response['type']->shouldBeString();
      $response['type']->shouldBe('email');
      $response->shouldHaveKey( 'status' );
      $response['status']->shouldBeString();

      $response->shouldHaveKey( 'template' );
      $response['template']->shouldBeArray();
      $response['template']->shouldHaveKey( 'id' );
      $response['template']['id']->shouldBeString();
      $response['template']->shouldHaveKey( 'version' );
      $response['template']['version']->shouldBeInteger();
      $response['template']->shouldHaveKey( 'uri' );
      $response['template']['uri']->shouldBeString();

      $response->shouldHaveKey( 'created_at' );
      $response->shouldHaveKey( 'sent_at' );
      $response->shouldHaveKey( 'completed_at' );

      self::$notificationId = $response['id']->getWrappedObject();

    }

    function it_receives_the_expected_response_when_sending_an_sms_notification(){

        $response = $this->sendSms( getenv('FUNCTIONAL_TEST_NUMBER'), getenv('SMS_TEMPLATE_ID'), [
            "name" => "Foo"
        ]);

        $response->shouldBeArray();
        $response->shouldHaveKey( 'id' );
        $response['id']->shouldBeString();

        $response->shouldHaveKey( 'reference' );

        $response->shouldHaveKey( 'content' );
        $response['content']->shouldBeArray();
        $response['content']->shouldHaveKey( 'from_number' );
        $response['content']['from_number']->shouldBeString();
        $response['content']->shouldHaveKey( 'body' );
        $response['content']['body']->shouldBeString();
        $response['content']['body']->shouldBe("Hello Foo\n\nFunctional Tests make our world a better place");

        $response->shouldHaveKey( 'template' );
        $response['template']->shouldBeArray();
        $response['template']->shouldHaveKey( 'id' );
        $response['template']['id']->shouldBeString();
        $response['template']->shouldHaveKey( 'version' );
        $response['template']['version']->shouldBeInteger();
        $response['template']->shouldHaveKey( 'uri' );

        $response->shouldHaveKey( 'uri' );
        $response['uri']->shouldBeString();

        self::$notificationId = $response['id']->getWrappedObject();

    }

    function it_receives_the_expected_response_when_looking_up_an_sms_notification() {

      // Requires the 'it_receives_the_expected_response_when_sending_an_sms_notification' test to have completed successfully
      if(is_null(self::$notificationId)) {
          throw new UnexpectedValueException('Notification ID not set');
      }

      $notificationId = self::$notificationId;

      // Retrieve sms notification by id and verify contents
      $response = $this->getNotification($notificationId);
      $response->shouldBeArray();
      $response->shouldHaveKey( 'id' );
      $response['id']->shouldBeString();

      $response->shouldHaveKey( 'body' );
      $response['body']->shouldBeString();
      $response['body']->shouldBe("Hello Foo\n\nFunctional Tests make our world a better place");
      $response->shouldHaveKey( 'subject' );

      $response->shouldHaveKey( 'reference' );
      $response->shouldHaveKey( 'email_address' );
      $response->shouldHaveKey( 'phone_number' );
      $response['phone_number']->shouldBeString();
      $response->shouldHaveKey( 'line_1' );
      $response->shouldHaveKey( 'line_2' );
      $response->shouldHaveKey( 'line_3' );
      $response->shouldHaveKey( 'line_4' );
      $response->shouldHaveKey( 'line_5' );
      $response->shouldHaveKey( 'line_6' );
      $response->shouldHaveKey( 'postcode' );
      $response->shouldHaveKey( 'type' );
      $response['type']->shouldBeString();
      $response['type']->shouldBe('sms');
      $response->shouldHaveKey( 'status' );
      $response['status']->shouldBeString();

      $response->shouldHaveKey( 'template' );
      $response['template']->shouldBeArray();
      $response['template']->shouldHaveKey( 'id' );
      $response['template']['id']->shouldBeString();
      $response['template']->shouldHaveKey( 'version' );
      $response['template']['version']->shouldBeInteger();
      $response['template']->shouldHaveKey( 'uri' );
      $response['template']['uri']->shouldBeString();

      $response->shouldHaveKey( 'created_at' );
      $response->shouldHaveKey( 'sent_at' );
      $response->shouldHaveKey( 'completed_at' );

    }

    function it_receives_the_expected_response_when_looking_up_all_notifications() {

      // Retrieve all notifications and verify each is correct (email & sms)
      $response = $this->listNotifications();

      $response->shouldHaveKey('links');
      $response['links']->shouldBeArray();

      $response->shouldHaveKey('notifications');
      $response['notifications']->shouldBeArray();

      $notifications = $response['notifications'];
      $total_notifications_count = count($notifications->getWrappedObject());

      for( $i = 0; $i < $total_notifications_count; $i++ ) {

          $notification = $notifications[$i];

          $notification->shouldBeArray();
          $notification->shouldHaveKey( 'id' );
          $notification['id']->shouldBeString();

          $notification->shouldHaveKey( 'reference' );
          $notification->shouldHaveKey( 'email_address' );
          $notification->shouldHaveKey( 'phone_number' );
          $notification->shouldHaveKey( 'line_1' );
          $notification->shouldHaveKey( 'line_2' );
          $notification->shouldHaveKey( 'line_3' );
          $notification->shouldHaveKey( 'line_4' );
          $notification->shouldHaveKey( 'line_5' );
          $notification->shouldHaveKey( 'line_6' );
          $notification->shouldHaveKey( 'postcode' );
          $notification->shouldHaveKey( 'status' );
          $notification['status']->shouldBeString();

          $notification->shouldHaveKey( 'template' );
          $notification['template']->shouldBeArray();
          $notification['template']->shouldHaveKey( 'id' );
          $notification['template']['id']->shouldBeString();
          $notification['template']->shouldHaveKey( 'version' );
          $notification['template']['version']->shouldBeInteger();
          $notification['template']->shouldHaveKey( 'uri' );
          $notification['template']['uri']->shouldBeString();

          $notification->shouldHaveKey( 'created_at' );
          $notification->shouldHaveKey( 'sent_at' );
          $notification->shouldHaveKey( 'completed_at' );

          $notification->shouldBeArray();

          $notification->shouldHaveKey( 'type' );
          $notification['type']->shouldBeString();
          $notification['type']->shouldBeString();
          $notification_type = $notification['type']->getWrappedObject();

          if ( $notification_type == "sms" ) {

            $notification['phone_number']->shouldBeString();

          } elseif ( $notification_type == "email") {

            $notification['email_address']->shouldBeString();

          }
      }

    }

}
