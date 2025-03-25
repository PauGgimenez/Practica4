#include <stdio.h>
#include <stdlib.h>
#include <time.h>

#define ARRAY_SIZE 100

// Function prototypes
void fillArray(int arr[], int size);
void printArray(const int arr[], int size);
int findMax(const int arr[], int size);
int findMin(const int arr[], int size);
double calculateAverage(const int arr[], int size);
void sortArray(int arr[], int size);
void swap(int *a, int *b);

int main() {
    int numbers[ARRAY_SIZE];
    srand((unsigned int)time(NULL)); // Seed for random number generation

    // Fill the array with random numbers
    fillArray(numbers, ARRAY_SIZE);

    // Print the original array
    printf("Original Array:\n");
    printArray(numbers, ARRAY_SIZE);

    // Find and print the maximum value
    int max = findMax(numbers, ARRAY_SIZE);
    printf("\nMaximum Value: %d\n", max);

    // Find and print the minimum value
    int min = findMin(numbers, ARRAY_SIZE);
    printf("Minimum Value: %d\n", min);

    // Calculate and print the average
    double average = calculateAverage(numbers, ARRAY_SIZE);
    printf("Average Value: %.2f\n", average);

    // Sort the array
    sortArray(numbers, ARRAY_SIZE);

    // Print the sorted array
    printf("\nSorted Array:\n");
    printArray(numbers, ARRAY_SIZE);

    return 0;
}

// Function to fill an array with random numbers
void fillArray(int arr[], int size) {
    for (int i = 0; i < size; i++) {
        arr[i] = rand() % 1000; // Random numbers between 0 and 999
    }
}

// Function to print an array
void printArray(const int arr[], int size) {
    for (int i = 0; i < size; i++) {
        printf("%d ", arr[i]);
        if ((i + 1) % 10 == 0) {
            printf("\n");
        }
    }
}

// Function to find the maximum value in an array
int findMax(const int arr[], int size) {
    int max = arr[0];
    for (int i = 1; i < size; i++) {
        if (arr[i] > max) {
            max = arr[i];
        }
    }
    return max;
}

// Function to find the minimum value in an array
int findMin(const int arr[], int size) {
    int min = arr[0];
    for (int i = 1; i < size; i++) {
        if (arr[i] < min) {
            min = arr[i];
        }
    }