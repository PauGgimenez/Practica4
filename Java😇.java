public class JavaSmiley {

    public static void main(String[] args) {
        System.out.println("Welcome to the 100-line Java program!");
        printDivider();
        printNumbers();
        printDivider();
        printFibonacci(20);
        printDivider();
        printPrimeNumbers(50);
        printDivider();
        printPattern(10);
        printDivider();
        System.out.println("End of the program. Thank you!");
    }

    private static void printDivider() {
        System.out.println("========================================");
    }

    private static void printNumbers() {
        System.out.println("Printing numbers from 1 to 20:");
        for (int i = 1; i <= 20; i++) {
            System.out.print(i + " ");
        }
        System.out.println();
    }

    private static void printFibonacci(int count) {
        System.out.println("First " + count + " Fibonacci numbers:");
        int a = 0, b = 1;
        for (int i = 0; i < count; i++) {
            System.out.print(a + " ");
            int next = a + b;
            a = b;
            b = next;
        }
        System.out.println();
    }

    private static void printPrimeNumbers(int limit) {
        System.out.println("Prime numbers up to " + limit + ":");
        for (int i = 2; i <= limit; i++) {
            if (isPrime(i)) {
                System.out.print(i + " ");
            }
        }
        System.out.println();
    }

    private static boolean isPrime(int number) {
        if (number < 2) {
            return false;
        }
        for (int i = 2; i <= Math.sqrt(number); i++) {
            if (number % i == 0) {
                return false;
            }
        }
        return true;
    }

    private static void printPattern(int rows) {
        System.out.println("Printing a triangle pattern with " + rows + " rows:");
        for (int i = 1; i <= rows; i++) {
            for (int j = 1; j <= i; j++) {
                System.out.print("* ");
            }
            System.out.println();
        }
    }
}