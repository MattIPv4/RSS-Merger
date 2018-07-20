# A super simple python script to build the required uri for the php script from a list of URLS

# Define urls
urls = [
    "https://blog.jetbrains.com/feed/",
    "https://blog.jetbrains.com/idea/feed/",
    "https://blog.jetbrains.com/dotnet/feed/",
    "https://blog.jetbrains.com/ruby/feed/",
    "https://blog.jetbrains.com/phpstorm/feed/",
    "https://blog.jetbrains.com/webstorm/feed/",
    "https://blog.jetbrains.com/teamcity/feed/",
    "https://blog.jetbrains.com/upsource/feed/",
    "https://blog.jetbrains.com/youtrack/feed/",
    "https://blog.jetbrains.com/pycharm/feed/",
    "https://blog.jetbrains.com/scala/feed/",
    "https://blog.jetbrains.com/objc/feed/",
    "https://blog.jetbrains.com/kotlin/feed/",
    "https://blog.jetbrains.com/rscpp/feed/",
    "https://blog.jetbrains.com/clion/feed/",
    "https://blog.jetbrains.com/datagrip/feed/",
    "https://blog.jetbrains.com/go/feed/",
    "https://blog.jetbrains.com/mps/feed/",
    "https://blog.jetbrains.com/hub/feed/",
]

# Add url arg to them
urls = ["feeds[]={}".format(f) for f in urls]

# Join them
urls = "&".join(urls)

# Export
print("?{}".format(urls))
