# Liste complète des 59 Tests Unitaires

**Tests des Contrôleurs (CultureControllerTest)**
1. testIndexPageIsSuccessful
2. testNewPageIsSuccessful
3. testNewFormContainsExpectedFields
4. testNewCultureSubmitRedirects
5. testIndexPageContainsHtmlBody
6. testNotFoundRoute

**Tests des Contrôleurs (EquipementControllerTest)**
7. testEquipementIndexDisplaysSuccessfully
8. testEquipementIndexWithSearchQuery
9. testEquipementIndexWithSortParameters
10. testEquipementNewFormDisplays
11. testEquipementShowWith404

**Tests des Contrôleurs (ParcelleControllerTest)**
12. testIndexPageIsSuccessful
13. testNewPageIsSuccessful
14. testNewFormContainsExpectedFields
15. testNewParcelleSubmitRedirects
16. testIndexPageContainsTitle
17. testNotFoundRoute

**Tests des Contrôleurs (ProductControllerTest)**
18. testSetCurrencyStoresInSessionAndRedirects
19. testSetCurrencyIgnoresInvalidCurrency

**Tests des Contrôleurs (ReviewControllerTest)**
20. testReviewIndexDisplaysSuccessfully
21. testReviewIndexWithSearchQuery
22. testReviewIndexWithSortByDate
23. testReviewIndexWithSortByNote
24. testReviewNewFormDisplays
25. testReviewShowWith404

**Tests des Services (ChatbotServiceTest)**
26. testGreetingQueryReturnsGreetingType
27. testCategoryQueryLegumesMapsToVegetables

**Tests des Services (CultureManagerTest)**
28. testCultureValide
29. testCultureValideSansDates
30. testCultureNomVide
31. testCultureDateRecolteEgalePlantation
32. testCultureDateRecolteAvantPlantation

**Tests des Services (EquipementManagerTest)**
33. testValidEquipement
34. testEquipementWithoutName
35. testEquipementWithNullName
36. testEquipementWithNegativePrice
37. testEquipementWithZeroPrice
38. testEquipementWithoutType
39. testCalculateDiscountedPriceAbove500
40. testCalculateDiscountedPriceBelow500
41. testIsAvailableEquipement
42. testIsNotAvailableEquipement

**Tests des Services (ParcelleManagerTest)**
43. testParcellValide
44. testParcelleNomVide
45. testParcelleSuperficieNulle
46. testParcelleSuperficieNegative

**Tests des Services (ReviewManagerTest)**
47. testValidReview
48. testReviewWithNoteToLow
49. testReviewWithNoteTooHigh
50. testReviewWithoutNote
51. testReviewWithoutCommentaire
52. testReviewWithNullCommentaire
53. testReviewWithoutEquipement
54. testCalculateAverageRating
55. testCalculateAverageRatingVaried
56. testCalculateAverageRatingEmpty
57. testIsPositiveReview
58. testIsNegativeReview
59. testIsExcellentReview
