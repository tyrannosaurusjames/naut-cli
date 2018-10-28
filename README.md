# naut-cli

A cli client for SilverStripe's deploynaut.

## Install

Download the latest phar from the releases section of this repo and put it somewhere
on your `$PATH`.

Or you can clone this repository and build the phar yourself by following
the instructions in the Build section below.
    
## Usage

There are a few different commands that can be run.

### Configure

naut-cli relies on a configuration file existing in your home directory.

You can create this by running:

    php naut-cli.phar configure
    
This will ask you for:

- The domain of deploynaut (e.g. https://deploy.test.com)
- Your username (e.g. test@test.com)
- Your deploynaut API token

And create a new file at `~/.naut.env` that is only readable by the current user.

### Deploy

Deploy the latest commit of a project to a specific environment with deploy:

    php naut-cli.phar deploy <project_id> <environment> <branch_name>
    
Where:

- `<project_id>` is the short text identifier of the project in deploynaut (also seen in the deploynaut URL at `/naut/project/<project_id>`)
- `<environment>` is the name of the environment you want to deploy to (e.g. '`prod`', '`uat`', '`test1`')
- `<branch_name>` is the name of the git branch you would like to deploy

A few things will happen when you run this command:

1. Fetch latest changes from git
2. Trigger deployment
3. Stream the deployment log back to your terminal

### Snapshots

#### List

You can see a list of snapshots for a specific stack with the command:

    php naut-cli.phar snapshot:list <stack_id>

This will display a table containing columns for: the snapshot id, source environment, mode, size, and created date/time.

#### Delete

You can delete a snapshot with the command:

    php naut-cli.phar snapshot:delete <stack_id> <snapshot_id>

#### Create

You can create a new snapshot for a specific stack/environment with the command:

    php naut-cli.phar snapshot:create <stack_id> <snapshot_id>

Optionally, you can include the `--mode` flag. This flag sets the type of snapshot to create.

Valid options are:

- all
- db
- assets

The default if the `--mode` flag is missing is `all`. Example usage: `--mode=assets`.

#### Download

You can download a snapshot with the command:

    php naut-cli.phar snapshot:download <stack_id> <snapshot_id>

By default, this command will save the snapshot to the current directory with the same name as it exists within the dashboard.
It will also display a progress bar while downloading by default.

If you would like to save to a different location/filename then you can use he `--to-stdout` option, and run the command like:

    php naut-cli.phar snapshot:download <stack_id> <snapshot_id> --to-stdout > /path/to/your/file.sspak

When downloading this way the progress bar is not displayed.

## Build phar from source

To build the `naut-cli.phar` file from source run the script `bin/build-phar.sh`.

This will create a fresh copy of the phar at `dist/naut-cli.phar`.

## To do

- Configurable via environment variables rather than .env file
- Implement more of the deploynaut API
