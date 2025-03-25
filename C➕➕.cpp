#include <iostream>
#include <vector>
#include <string>
#include <algorithm>
#include <cmath>

using namespace std;

// Function to calculate the factorial of a number
unsigned long long factorial(int n) {
    if (n <= 1) return 1;
    return n * factorial(n - 1);
}

// Function to check if a number is prime
bool isPrime(int n) {
    if (n <= 1) return false;
    for (int i = 2; i <= sqrt(n); ++i) {
        if (n % i == 0) return false;
    }
    return true;
}

// Function to reverse a string
string reverseString(const string& str) {
    string reversed = str;
    reverse(reversed.begin(), reversed.end());
    return reversed;
}

// Function to calculate the sum of elements in a vector
int sumVector(const vector<int>& vec) {
    int sum = 0;
    for (int num : vec) {
        sum += num;
    }
    return sum;
}

// Function to find the maximum element in a vector
int maxVector(const vector<int>& vec) {
    return *max_element(vec.begin(), vec.end());
}

// Function to print a vector
void printVector(const vector<int>& vec) {
    for (int num : vec) {
        cout << num << " ";
    }
    cout << endl;
}

// Function to generate Fibonacci sequence up to n terms
vector<int> generateFibonacci(int n) {
    vector<int> fib;
    if (n <= 0) return fib;
    fib.push_back(0);
    if (n == 1) return fib;
    fib.push_back(1);
    for (int i = 2; i < n; ++i) {
        fib.push_back(fib[i - 1] + fib[i - 2]);
    }
    return fib;
}

// Function to check if a string is a palindrome
bool isPalindrome(const string& str) {
    string reversed = reverseString(str);
    return str == reversed;
}

// Main function
int main() {
    cout << "Factorial of 5: " << factorial(5) << endl;

    cout << "Is 7 prime? " << (isPrime(7) ? "Yes" : "No") << endl;

    string testString = "hello";
    cout << "Reversed string of 'hello': " << reverseString(testString) << endl;

    vector<int> numbers = {1, 2, 3, 4, 5};
    cout << "Sum of vector: " << sumVector(numbers) << endl;
    cout << "Max of vector: " << maxVector(numbers) << endl;

    cout << "Vector elements: ";
    printVector(numbers);

    int fibTerms = 10;
    vector<int> fibonacci = generateFibonacci(fibTerms);
    cout << "Fibonacci sequence up to " << fibTerms << " terms: ";
    printVector(fibonacci);

    string palindromeTest = "radar";
    cout << "Is 'radar' a palindrome? " << (isPalindrome(palindromeTest) ? "Yes" : "No") << endl;

    return 0;
}