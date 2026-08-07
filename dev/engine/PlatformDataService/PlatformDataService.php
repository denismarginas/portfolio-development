<?php

class PlatformDataService
{
    use PlatformDataServiceCore, PlatformDataServicePosts, PlatformDataServiceItems, PlatformDataServiceComponents;

    private static array $cache = [];
    private static ?string $data_dir = null;
    private static ?string $language = null;
}
