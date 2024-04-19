# **Step-by-Step Tutorial on Using OBS Teleport Plugin**

## **Prerequisites:**

* Two laptops with OBS Studio installed and configured
* OBS Teleport plugin installed on both laptops
* Both laptops connected to the same local network
* Network firewalls disabled or configured to allow connections on the specified port (if necessary)
* A stable 1 Gbps network connection (recommended)

## **Step 1: Configure the Sender Laptop**

## Setup Sender

### Select a detected stream from the Teleport drop down.

1. Open OBS Studio on the sender laptop.
2. Go to **Settings** > **Plugins** and ensure that the OBS Teleport plugin is enabled.
3. In the **Settings** > **Stream** tab, select the scene you want to transmit to the receiver laptop.
4. Click on the **Teleport** button in the **Sources** panel.
5. In the **Teleport** window, select the **Sender** mode.
6. Set the **Port** to a specific value (e.g., 12345) if you want to force the sender to listen on a specific port. Otherwise, leave it blank.
7. Click **OK** to save the changes.

#### Go to Tools → Teleport.

![Setup Sender](https://github.com/fzwoch/obs-teleport/raw/main/img/teleport-tools.png)

#### Check Teleport Enabled.

[Check Teleport Enabled](https://github.com/fzwoch/obs-teleport/raw/main/img/teleport-output.png)

### Setup Sender as Audio/Video Filter

#### Click <Source> Right click → Filters.

[Filters](https://github.com/fzwoch/obs-teleport/raw/main/img/teleport-properties.png)

#### Click + → Teleport.

[Teleport](https://github.com/fzwoch/obs-teleport/raw/main/img/teleport-filter.png)

## Setup Receiver

### **Step 2: Configure the Receiver Laptop**

1. Open OBS Studio on the receiver laptop.
2. Go to **Settings** > **Plugins** and ensure that the OBS Teleport plugin is enabled.
3. In the **Settings** > **Stream** tab, select the scene where you want to receive the transmitted video and audio.
4. Click on the **Teleport** button in the **Sources** panel.
5. In the **Teleport** window, select the **Receiver** mode.
6. Enter the IP address of the sender laptop in the **Host** field.
7. Enter the port number specified in Step 1 (if you forced the sender to listen on a specific port).
8. Click **OK** to save the changes.

#### In your Scene do Sources → Add → Teleport.

[Setup Receiver](https://github.com/fzwoch/obs-teleport/raw/main/img/teleport-add.png)

#### Select a detected stream from the Teleport drop down.

[](https://github.com/fzwoch/obs-teleport/raw/main/img/teleport-source.png)

## **Step 3: Establish the Connection**

1. On the sender laptop, click the **Start Teleport** button in the **Teleport** window.
2. On the receiver laptop, click the **Connect** button in the **Teleport** window.
3. The sender laptop will start transmitting the selected scene to the receiver laptop.

**Step 4: Verify the Connection**

1. On the receiver laptop, verify that the transmitted video and audio are being received correctly.
2. Check the **Teleport** window on both laptops to ensure that the connection is established and the data is being transmitted.

### **Troubleshooting Tips:**

* If no discovery is working or no video/audio is being transmitted, ensure that network firewalls are disabled or configured to allow connections on the specified port.
* If you're experiencing issues with the connection, try restarting both laptops or checking the network connection stability.
* Experiment with different quality settings and bandwidth limitations to optimize the transmission for your specific use case.

#### **Additional Notes:**

* As of now, only the Audio/Video filter mechanic is implemented on the filter feature. Adding it as an effect filter is currently not supported. Revert to the output mode in this case.
* Refer to the OBS Studio documentation for general plugin installation and configuration guidelines.

By following these steps, you should be able to successfully use the OBS Teleport plugin to transmit video and audio between two laptops on the same local network.

## Installation Plugin
Please refer to the OBS Studio documentation on how and where to install plugins. There are too many platforms and installation options available as the scope of this project could explain and maintain.

Most platforms do have an installer though that may help you with the installation.

Binaries can be grabbed from the Releases section.
