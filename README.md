# Backblaze B2 API library for PHP

This library is designed to allow you to plug in your own PSR18 compliant HTTP client in order to utilize the Backblaze B2 API.  As originally created, this was created to provide the backbone for a flysystem adapter, thus not all API calls are yet completed.

## Usage

    <?php
    use Backblaze\B2\ApiClient;

    $client = new ApiClient($accountId, $apiKey);

    foreach($client->listFilenames() as $item) {
        printf("File: %s\n", $item->getFileName());
    }

### Client Constructor
    $client = new ApiClient(private string $applicationKeyId,
        private string $applicationKey, ClientInterface $httpClient = null,
        RequestFactoryInterface $requestFactory = null,
        StreamFactoryInterface $streamFactory = null
    )

The only required values are an application key id (or master ID) and an application key.  You can pass in your own PSR18 HTTP Client as well as your own request and stream factories.  This allows you to add plugins such as connection logging, etc in your process without bogging down this library with all kinds of additional baggage.  This also prevents this library from being tied to a specific client implementation that then creates conflicts with your own project that might use a different version leading to dependancy nightmares.

If you use the symfony/http-client as your implementation, you can pass the same object as the $httpClient, $requestFactory and $streamFactory.
