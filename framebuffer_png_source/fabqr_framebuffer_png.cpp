//    This file is part of FabQR. (https://github.com/FroChr123/FabQR)
//
//    FabQR is free software: you can redistribute it and/or modify
//    it under the terms of the GNU Lesser General Public License as published by
//    the Free Software Foundation, either version 3 of the License, or
//    (at your option) any later version.
//
//    FabQR is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU General Public License for more details.
//
//    You should have received a copy of the GNU Lesser General Public License
//    along with FabQR.  If not, see <http://www.gnu.org/licenses/>.

#include <sys/file.h>
#include <sys/mman.h>
#include <sys/ioctl.h>
#include <linux/fb.h>
#include <stdio.h>
#include <stdint.h>
#include <stdlib.h>
#include <time.h>
#include "lodepng.h"

// Argument 1: Debug output text message
// void debugmsg(const char *msg)
// {
//     printf(msg);
// }

// Argument 0: Execution command for this program
// Argument 1: Output framebuffer device (e.g.: /dev/fb0)
// Argument 2: Input picture PNG data (e.g.: /dev/shm/fabqr_graphics.png)
int main(int argc, char *argv[])
{
    // Debug messages
    // debugmsg("Program start\n");
    // debugmsg("Check argument count\n");

    // Quit if argument count does not match
    if (argc != 3)
    {
        return 1;
    }

    // Debug message
    // debugmsg("Argument count correct, initialize variables\n");

    // Framebuffer variables
    unsigned int fb_width_real = 0;
    unsigned int fb_width_virtual = 0;
    unsigned int fb_height_real = 0;
    unsigned int fb_pixel_bits = 0;
    unsigned int fb_pixel_bytes = 0;
    uint32_t fb_line_length = 0;
    uint32_t fb_memory_size = 0;
    int fb_file = -1;
    uint16_t *fb_data = NULL;
    std::string fb_path(argv[1]);

    // PNG variables
    unsigned int png_width = 0;
    unsigned int png_height = 0;
    int png_file = -1;
    unsigned char* png_data = NULL;
    std::string png_path(argv[2]);

    // Timer variables
    // 50 milliseconds
    struct timespec refreshtime;
    refreshtime.tv_sec = 0;
    refreshtime.tv_nsec = 50000000L;

    // 3 seconds
    struct timespec waittime;
    waittime.tv_sec = 3;
    waittime.tv_nsec = 0L;

    // Debug message
    // debugmsg("Variables initialized, open framebuffer\n");

    // Open framebuffer
    fb_file = open(fb_path.c_str(), O_RDWR);

    // Quit if framebuffer can not be opened
    if (fb_file == -1)
    {
        // Debug message
        // debugmsg("ERROR: Can not open framebuffer, quit\n");
        return 1;
    }

    // Debug message
    // debugmsg("Opened framebuffer, read framebuffer information\n");

    // Get current information of framebuffer, quit on error
    struct fb_var_screeninfo fb_var_screeninfo;
    struct fb_fix_screeninfo fb_fix_screeninfo;

    if (ioctl(fb_file, FBIOGET_VSCREENINFO, &fb_var_screeninfo) == -1)
    {
        // Debug message
        // debugmsg("ERROR: Can not get framebuffer var info, quit\n");
        return 1;
    }

    if (ioctl(fb_file, FBIOGET_FSCREENINFO, &fb_fix_screeninfo) == -1)
    {
        // Debug message
        // debugmsg("ERROR: Can not get framebuffer fix info, quit\n");
        return 1;
    }

    fb_pixel_bits = fb_var_screeninfo.bits_per_pixel;
    fb_line_length = fb_fix_screeninfo.line_length;
    fb_memory_size = fb_fix_screeninfo.smem_len;

    // Quit if amount of bits is not 16
    if (fb_pixel_bits != 16)
    {
        // Debug message
        // debugmsg("ERROR: Bits per pixel is not 16, quit\n");
        return 1;
    }

    // Compute amount of bytes from 16 bits
    fb_pixel_bytes = fb_pixel_bits / 8;

    // NOTICE: fb_var_screeninfo.xres describes the real resolution on screen
    // But there might be more virtual buffer allocated for each line
    fb_width_real = fb_var_screeninfo.xres;
    fb_width_virtual = fb_width_real;
    fb_height_real = fb_var_screeninfo.yres;

    // Compute virtual width from line length (bytes per line) if it is a multiple of bytes per pixel
    if (fb_line_length % fb_pixel_bytes == 0)
    {
        fb_width_virtual = fb_line_length / fb_pixel_bytes;
    }

    // Debug message
    // debugmsg("Framebuffer information read, read framebuffer data pointer\n");

    // Get pointer to framebuffer data
    fb_data = (uint16_t*)(mmap(0, fb_memory_size, PROT_READ | PROT_WRITE, MAP_SHARED, fb_file, 0));

    // Quit if pointer invalid
    if (fb_data == NULL || fb_data == MAP_FAILED)
    {
        // Debug message
        // debugmsg("ERROR: Framebuffer pointer invalid, quit\n");
        return 1;
    }

    // Debug message
    // debugmsg("Framebuffer data pointer read, write initial black image\n");

    // Write initial black image to framebuffer
    for (unsigned int fb_row = 0; fb_row < fb_height_real; ++fb_row)
        for (unsigned int fb_column = 0; fb_column < fb_width_virtual; ++fb_column)
            fb_data[fb_row * fb_width_virtual + fb_column] = 0;

    // Debug message
    // debugmsg("Initial black image written, start infinite loop\n");

    // Infinite loop to update displayed picture
    while (true)
    {
        // If file does not exist, output black image and wait
        if (access(png_path.c_str(), F_OK) == -1)
        {
            // Write black image to framebuffer
            for (unsigned int fb_row = 0; fb_row < fb_height_real; ++fb_row)
                for (unsigned int fb_column = 0; fb_column < fb_width_virtual; ++fb_column)
                    fb_data[fb_row * fb_width_virtual + fb_column] = 0;

            // Reset file pointer and sleep for waittime
            png_file = -1;
            nanosleep(&waittime, NULL);
            continue;
        }

        // Open png file if not yet done
        if (png_file == -1)
        {
            png_file = open(png_path.c_str(), O_RDWR);

            // Could not open png file again, sleep for a long time
            if (png_file == -1)
            {
                // Reset file pointer and sleep for waittime
                png_file = -1;
                nanosleep(&waittime, NULL);
                continue;
            }
        }

        // Lock png file, on error reset file pointer
        if (flock(png_file, LOCK_EX) == -1)
        {
            close(png_file);
            png_file = -1;
            continue;
        }

        // Read data from pngfile, on error reset file pointer
        if (lodepng_decode32_file(&png_data, &png_width, &png_height, png_path.c_str()))
        {
            close(png_file);
            png_file = -1;
            continue;
        }

        // Unlock png file, on error reset file pointer
        if (flock(png_file, LOCK_UN) == -1)
        {
            close(png_file);
            png_file = -1;
            continue;
        }

        // If read PNG size matches to framebuffer size, write each pixel to framebuffer
        if (png_width == fb_width_real && png_height == fb_height_real)
        {
            // Iterate each row
            for (unsigned int fb_row = 0; fb_row < fb_height_real; ++fb_row)
            {
                // Iterate each column
                for (unsigned int fb_column = 0; fb_column < fb_width_virtual; ++fb_column)
                {
                    // Result variable for framebuffer
                    uint16_t result = 0;

                    // Virtual bytes are located at the right end of each line, only process pixels which are
                    // left of width real, all additional bytes of virtual width are simply set to black
                    if (fb_column < fb_width_real)
                    {
                        // Compute and set pixel values in RGB565 encoding
                        uint8_t red = (uint8_t)(png_data[4 * fb_row * fb_width_real + 4 * fb_column]);
                        uint8_t green = (uint8_t)(png_data[4 * fb_row * fb_width_real + 4 * fb_column + 1]);
                        uint8_t blue = (uint8_t)(png_data[4 * fb_row * fb_width_real + 4 * fb_column + 2]);

                        // Shift values to fit RGB565 encoding
                        red = red >> 3;
                        green = green >> 2;
                        blue = blue >> 3;

                        // Shift values in their correct position in result
                        result = result | (red << 11);
                        result = result | (green << 5);
                        result = result | blue;
                    }

                    // Output data to framebuffer pointer
                    fb_data[fb_row * fb_width_virtual + fb_column] = result;
                }
            }
        }

        // Cleanup png data pointer
        free(png_data);

        // Sleep for refreshtime
        nanosleep(&refreshtime, NULL);
    }

    // NOTE: Usually the while loop should never be exited, thus the code below should never be executed
    // This application is usually closed by external commands (pkill)

    // Debug message
    // debugmsg("Infinite loop quit, release data pointer\n");

    // Release data pointer
    if (fb_data != NULL && fb_data != MAP_FAILED)
    {
        munmap(fb_data, fb_memory_size);
    }

    // Debug message
    // debugmsg("Data pointer released, close file pointers\n");

    // Close file pointers
    if (png_file != -1)
    {
        close(png_file);
    }

    if (fb_file != -1)
    {
        close(fb_file);
    }

    // Debug message
    // debugmsg("File pointers closed, quit program correctly\n");

    // Quit correctly
    return 0;
}
