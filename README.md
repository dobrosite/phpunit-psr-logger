# Инструменты для тестирования кода, использующего PSR-3

## Установка

    composer require --dev dobrosite/phpunit-psr-logger

## Подключение

В тестовой конфигурации вашего приложения вам надо подменить используемую реализацию
`Psr\Log\LoggerInterface` экземпляром `DobroSite\PHPUnit\PSR3\TestLogger`. Как это
сделать, зависит от устройства вашего приложения, ниже даны примеры для популярных фреймворков.

### Symfony

В конфигурацию тестового контейнера зависимостей (обычно — `config/services_test.yaml`) добавьте:

```yaml
services:

  logger:
    class: DobroSite\PHPUnit\PSR3\TestLogger
    public: true
```

Теперь в тесты, унаследованные от `Symfony\Bundle\FrameworkBundle\Test\KernelTestCase`, добавьте
примесь [TestLoggerForSymfony](src/Symfony/TestLoggerForSymfony.php):

```php
use DobroSite\PHPUnit\PSR3\Symfony\TestLoggerForSymfony;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class SomeTest extends WebTestCase
{
    use TestLoggerForSymfony;

    public function testSomething(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/api/foo');

        $this->assertResponseIsSuccessful();

        $this->getLogger()->getRecords()
            ->debug('Expected log message.')
            // …
            ->end;
    }
}
```
